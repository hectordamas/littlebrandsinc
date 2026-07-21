<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\{Student, User, Enrollment, Account};
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Throwable;


class StudentsController extends Controller
{
    public function importForm()
    {
        return view('students.import');
    }

    public function importStore(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv,txt|max:5120',
        ]);

        $file = $request->file('file');

        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
        } catch (Throwable $e) {
            return back()->withErrors(['file' => 'No se pudo leer el archivo. Verifica que sea un Excel/CSV valido.']);
        }

        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);

        if (count($rows) < 2) {
            return back()->withErrors(['file' => 'El archivo no contiene filas de datos para importar.']);
        }

        $headerRow = array_shift($rows);
        $mappedHeaders = $this->buildHeaderMap($headerRow);

        $requiredColumns = ['student_name', 'student_birthdate', 'parent_name', 'parent_email'];
        $missingColumns = [];

        foreach ($requiredColumns as $column) {
            if (!array_key_exists($column, $mappedHeaders)) {
                $missingColumns[] = $column;
            }
        }

        if (!empty($missingColumns)) {
            return back()->withErrors([
                'file' => 'Columnas faltantes en el archivo: ' . implode(', ', $missingColumns) . '. Revisa el formato de encabezados.',
            ]);
        }

        $stats = [
            'parents_created' => 0,
            'parents_updated' => 0,
            'students_created' => 0,
            'students_updated' => 0,
            'rows_skipped' => 0,
            'errors' => [],
        ];

        DB::beginTransaction();

        try {
            foreach ($rows as $index => $row) {
                $line = $index + 2;
                $result = $this->importRow($row, $mappedHeaders, $line);

                if (!$result['ok']) {
                    $stats['rows_skipped']++;

                    if (!empty($result['error'])) {
                        $stats['errors'][] = $result['error'];
                    }

                    continue;
                }

                $stats['parents_created'] += $result['parents_created'];
                $stats['parents_updated'] += $result['parents_updated'];
                $stats['students_created'] += $result['students_created'];
                $stats['students_updated'] += $result['students_updated'];
            }

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();

            return back()->withErrors([
                'file' => 'Ocurrio un error durante la importacion. Ningun cambio fue aplicado.',
            ]);
        }

        $message = sprintf(
            'Importacion completada. Representantes creados: %d, actualizados: %d. Estudiantes creados: %d, actualizados: %d. Filas omitidas: %d.',
            $stats['parents_created'],
            $stats['parents_updated'],
            $stats['students_created'],
            $stats['students_updated'],
            $stats['rows_skipped']
        );

        if (!empty($stats['errors'])) {
            $message .= ' Errores: ' . implode(' | ', array_slice($stats['errors'], 0, 5));
        }

        return redirect()->route('students.index')->with('success', $message);
    }

    public function index()
    {
        $students = Student::with([
            'user',
            'enrollments.program'
        ])->orderBy('id', 'desc')->get();

        return view('students.index', [
            'students' => $students
        ]);
    }

    public function show(Student $student)
    {
        $student->load([
            'user',
            'enrollments.receivable',
            'enrollments.transactions.account',
            'enrollments.courses.branch',
            'enrollments.courses.coaches',
            'enrollments.courses.classes' => function ($query) {
                $query->with('coach')->orderBy('date')->orderBy('start_time');
            },
        ]);

        foreach ($student->enrollments as $enrollment) {
            $enrollment->syncReceivable();
        }

        $upcomingClasses = $student->enrollments
            ->where('status', '!=', 'cancelled')
            ->flatMap(function ($enrollment) {
                return $enrollment->courses->flatMap(fn ($course) => $course->classes ?? collect());
            })
            ->filter(function ($class) {
                return $class->date && Carbon::parse($class->date)->greaterThanOrEqualTo(now()->startOfDay());
            })
            ->sortBy(function ($class) {
                return sprintf('%s %s', $class->date, $class->start_time);
            })
            ->values();

        $accounts = Account::where('active', true)->orderBy('name')->get();

        return view('students.show', [
            'student' => $student,
            'upcomingClasses' => $upcomingClasses,
            'accounts' => $accounts,
        ]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'whatsapp' => 'required',
            'password' => 'required|confirmed|min:6',
            'student_name' => 'required',
            'birthdate' => 'required|date',
            'terms' => 'accepted',
            'program_id' => 'required|integer|exists:programs,id',
            'course_ids' => 'required|array|min:1',
            'course_ids.*' => 'integer|exists:courses,id',
        ]);

        if ($request->user_type === 'existing') {
            $user = User::where('email', $request->email_login)->first();

            if (!$user || !Hash::check($request->password_login, $user->password)) {
                return back()->withErrors(['email_login' => 'Credenciales incorrectas']);
            }
        } else {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'whatsapp' => $request->whatsapp,
                'password' => Hash::make($request->password),
            ]);
        }

        // Login automático
        Auth::login($user);

        // Crear estudiante
        $student = Student::create([
            'user_id' => $user->id,
            'name' => $request->student_name,
            'birthdate' => $request->birthdate,
            'medical_notes' => $request->medical_notes,
        ]);

        // Crear inscripción con programa y padre
        $enrollment = Enrollment::create([
            'student_id' => $student->id,
            'program_id' => $request->program_id,
            'parent_id' => $user->id,
            'status' => 'active',
            'terms_accepted' => true,
        ]);

        // Sincronizar cursos seleccionados en la pivot
        $enrollment->courses()->sync($request->course_ids);

        return redirect()->route('home')->with('success', 'Inscripción completada');
    }

    private function importRow(array $row, array $mappedHeaders, int $line): array
    {
        $studentName = $this->getValue($row, $mappedHeaders, 'student_name');
        $birthdateRaw = $this->getValue($row, $mappedHeaders, 'student_birthdate');
        $parentName = $this->getValue($row, $mappedHeaders, 'parent_name');
        $parentEmail = mb_strtolower($this->getValue($row, $mappedHeaders, 'parent_email'));

        $isEmptyRow = $studentName === '' && $birthdateRaw === '' && $parentName === '' && $parentEmail === '';
        if ($isEmptyRow) {
            return ['ok' => false, 'error' => null, 'parents_created' => 0, 'parents_updated' => 0, 'students_created' => 0, 'students_updated' => 0];
        }

        if ($studentName === '' || $birthdateRaw === '' || $parentName === '' || $parentEmail === '') {
            return [
                'ok' => false,
                'error' => "Fila {$line}: faltan campos obligatorios (student_name, student_birthdate, parent_name, parent_email).",
                'parents_created' => 0,
                'parents_updated' => 0,
                'students_created' => 0,
                'students_updated' => 0,
            ];
        }

        if (!filter_var($parentEmail, FILTER_VALIDATE_EMAIL)) {
            return [
                'ok' => false,
                'error' => "Fila {$line}: email de representante invalido ({$parentEmail}).",
                'parents_created' => 0,
                'parents_updated' => 0,
                'students_created' => 0,
                'students_updated' => 0,
            ];
        }

        $birthdate = $this->parseDate($birthdateRaw);
        if ($birthdate === null) {
            return [
                'ok' => false,
                'error' => "Fila {$line}: fecha de nacimiento invalida ({$birthdateRaw}).",
                'parents_created' => 0,
                'parents_updated' => 0,
                'students_created' => 0,
                'students_updated' => 0,
            ];
        }

        $dialCode = $this->getValue($row, $mappedHeaders, 'parent_dial_code');
        $whatsapp = $this->getValue($row, $mappedHeaders, 'parent_whatsapp');
        $studentMedicalNotes = $this->getValue($row, $mappedHeaders, 'student_medical_notes');
        $studentComment = $this->getValue($row, $mappedHeaders, 'student_comment');
        $activeValue = $this->getValue($row, $mappedHeaders, 'student_active');
        $active = $this->toBoolean($activeValue, true);

        $parentsCreated = 0;
        $parentsUpdated = 0;
        $studentsCreated = 0;
        $studentsUpdated = 0;

        $user = User::whereRaw('LOWER(email) = ?', [$parentEmail])->first();
        if (!$user) {
            $user = User::create([
                'name' => $parentName,
                'email' => $parentEmail,
                'dial_code' => $dialCode ?: null,
                'whatsapp' => $whatsapp ?: null,
                'password' => Hash::make(Str::random(14)),
                'role' => 'Padre',
            ]);

            $parentsCreated++;
        } else {
            $wasUpdated = false;

            if ($user->name !== $parentName) {
                $user->name = $parentName;
                $wasUpdated = true;
            }

            if ($dialCode !== '' && $user->dial_code !== $dialCode) {
                $user->dial_code = $dialCode;
                $wasUpdated = true;
            }

            if ($whatsapp !== '' && $user->whatsapp !== $whatsapp) {
                $user->whatsapp = $whatsapp;
                $wasUpdated = true;
            }

            if (mb_strtolower((string) $user->role) !== 'padre') {
                $user->role = 'Padre';
                $wasUpdated = true;
            }

            if ($wasUpdated) {
                $user->save();
                $parentsUpdated++;
            }
        }

        $student = Student::where('user_id', $user->id)
            ->whereDate('birthdate', $birthdate)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($studentName)])
            ->first();

        if (!$student) {
            Student::create([
                'user_id' => $user->id,
                'name' => $studentName,
                'birthdate' => $birthdate,
                'medical_notes' => $studentMedicalNotes ?: null,
                'comment' => $studentComment ?: null,
                'active' => $active,
            ]);

            $studentsCreated++;
        } else {
            $wasStudentUpdated = false;

            if ($studentMedicalNotes !== '' && $student->medical_notes !== $studentMedicalNotes) {
                $student->medical_notes = $studentMedicalNotes;
                $wasStudentUpdated = true;
            }

            if ($studentComment !== '' && $student->comment !== $studentComment) {
                $student->comment = $studentComment;
                $wasStudentUpdated = true;
            }

            if ((bool) $student->active !== (bool) $active) {
                $student->active = $active;
                $wasStudentUpdated = true;
            }

            if ($wasStudentUpdated) {
                $student->save();
                $studentsUpdated++;
            }
        }

        return [
            'ok' => true,
            'error' => null,
            'parents_created' => $parentsCreated,
            'parents_updated' => $parentsUpdated,
            'students_created' => $studentsCreated,
            'students_updated' => $studentsUpdated,
        ];
    }

    private function buildHeaderMap(array $headerRow): array
    {
        $aliases = [
            'student_name' => ['student_name', 'nombre_estudiante', 'nombre_del_estudiante', 'estudiante', 'alumno', 'nombre alumno', 'nombre', 'nombre_participante'],
            'student_birthdate' => ['student_birthdate', 'birthdate', 'fecha_nacimiento', 'fecha_de_nacimiento', 'nacimiento', 'fecha nacimiento', 'fecha de nac', 'fecha_de_nac'],
            'student_medical_notes' => ['student_medical_notes', 'medical_notes', 'notas_medicas', 'observaciones_medicas', 'condiciones_medicas'],
            'student_comment' => ['student_comment', 'comment', 'comentario', 'comentarios', 'observaciones'],
            'student_active' => ['student_active', 'active', 'activo', 'estatus', 'estado'],
            'parent_name' => ['parent_name', 'representante_nombre', 'nombre_representante', 'representante', 'padre_nombre', 'nombre_padre', 'nombre_representante_legal'],
            'parent_email' => ['parent_email', 'representante_email', 'email_representante', 'correo_representante', 'email', 'correo'],
            'parent_dial_code' => ['parent_dial_code', 'dial_code', 'codigo_pais', 'codigo', 'cod_pais'],
            'parent_whatsapp' => ['parent_whatsapp', 'representante_whatsapp', 'telefono_representante', 'whatsapp_representante', 'telefono', 'celular', 'whatsapp'],
        ];

        $map = [];

        foreach ($headerRow as $index => $header) {
            $normalized = $this->normalizeHeader((string) $header);

            foreach ($aliases as $target => $options) {
                foreach ($options as $option) {
                    if ($normalized === $this->normalizeHeader($option)) {
                        $map[$target] = $index;
                        continue 3;
                    }
                }
            }
        }

        return $map;
    }

    private function normalizeHeader(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = str_replace(['á', 'é', 'í', 'ó', 'ú', 'ñ'], ['a', 'e', 'i', 'o', 'u', 'n'], $value);
        $value = preg_replace('/[^a-z0-9]+/u', '_', $value) ?? '';

        return trim($value, '_');
    }

    private function getValue(array $row, array $mappedHeaders, string $column): string
    {
        if (!array_key_exists($column, $mappedHeaders)) {
            return '';
        }

        $index = $mappedHeaders[$column];
        $value = $row[$index] ?? '';

        if (is_string($value)) {
            return trim($value);
        }

        if (is_numeric($value)) {
            return trim((string) $value);
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        return '';
    }

    private function parseDate(string $value): ?string
    {
        if ($value === '') {
            return null;
        }

        if (is_numeric($value)) {
            try {
                return ExcelDate::excelToDateTimeObject((float) $value)->format('Y-m-d');
            } catch (Throwable $e) {
                return null;
            }
        }

        $value = trim($value);
        $formats = [];

        if (str_contains($value, '/')) {
            $parts = explode('/', $value);
            if (count($parts) === 3) {
                if (strlen(trim($parts[2])) === 2) {
                    $formats = ['d/m/y', 'd/m/Y'];
                } else {
                    $formats = ['d/m/Y', 'd/m/y'];
                }
            }
        } elseif (str_contains($value, '-')) {
            $parts = explode('-', $value);
            if (count($parts) === 3) {
                if (strlen(trim($parts[0])) === 4) {
                    $formats = ['Y-m-d', 'd-m-Y', 'd-m-y'];
                } else if (strlen(trim($parts[2])) === 2) {
                    $formats = ['d-m-y', 'd-m-Y', 'Y-m-d'];
                } else {
                    $formats = ['d-m-Y', 'd-m-y', 'Y-m-d'];
                }
            }
        }

        if (empty($formats)) {
            $formats = ['Y-m-d', 'd/m/Y', 'd/m/y', 'd-m-Y', 'd-m-y'];
        }

        foreach ($formats as $format) {
            try {
                $date = Carbon::createFromFormat($format, $value);
                if (str_contains($format, 'Y') && $date->year < 100) {
                    continue;
                }
                return $date->format('Y-m-d');
            } catch (Throwable $e) {
                // Intentar con el siguiente formato
            }
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (Throwable $e) {
            return null;
        }
    }

    private function toBoolean(string $value, bool $default): bool
    {
        $normalized = mb_strtolower(trim($value));

        if ($normalized === '') {
            return $default;
        }

        $truthy = ['1', 'true', 'si', 'sí', 'yes', 'activo', 'activa'];
        $falsy = ['0', 'false', 'no', 'inactivo', 'inactiva'];

        if (in_array($normalized, $truthy, true)) {
            return true;
        }

        if (in_array($normalized, $falsy, true)) {
            return false;
        }

        return $default;
    }
}
