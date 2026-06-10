<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{User, Student, Enrollment, Course, Program, Branch};
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Carbon\Carbon;
use Throwable;

class ExcelImportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filePath = base_path('Importar Estudiantes y Representantes.xlsx');
        
        if (!file_exists($filePath)) {
            $this->command->error("El archivo excel no existe en la raiz del proyecto: " . $filePath);
            return;
        }

        $this->command->info("Cargando archivo Excel para importación de representantes y alumnos...");

        try {
            $spreadsheet = IOFactory::load($filePath);
            $sheetNames = $spreadsheet->getSheetNames();
        } catch (Throwable $e) {
            $this->command->error("No se pudo leer el archivo Excel: " . $e->getMessage());
            return;
        }

        $createdParents = 0;
        $createdStudents = 0;
        $createdEnrollments = 0;

        foreach ($sheetNames as $sheetName) {
            $this->command->info("Procesando hoja: '{$sheetName}'...");
            $sheet = $spreadsheet->getSheetByName($sheetName);
            $rows = $sheet->toArray(null, true, true, false);

            if (count($rows) < 2) {
                $this->command->warn("La hoja '{$sheetName}' no contiene suficientes filas de datos. Omitiendo.");
                continue;
            }

            $headerRow = array_shift($rows);
            $mappedHeaders = $this->buildHeaderMap($headerRow);

            $requiredColumns = ['student_name', 'student_birthdate', 'parent_name', 'parent_email'];
            $hasAllColumns = true;
            foreach ($requiredColumns as $column) {
                if (!array_key_exists($column, $mappedHeaders)) {
                    $this->command->warn("La hoja '{$sheetName}' no tiene la columna requerida: " . $column . ". Omitiendo hoja.");
                    $hasAllColumns = false;
                    break;
                }
            }

            if (!$hasAllColumns) {
                continue;
            }

            foreach ($rows as $index => $row) {
                $line = $index + 2;

                $studentName = $this->getValue($row, $mappedHeaders, 'student_name');
                $birthdateRaw = $this->getValue($row, $mappedHeaders, 'student_birthdate');
                $parentName = $this->getValue($row, $mappedHeaders, 'parent_name');
                $parentEmail = mb_strtolower($this->getValue($row, $mappedHeaders, 'parent_email'));

                $isEmpty = $studentName === '' && $birthdateRaw === '' && $parentName === '' && $parentEmail === '';
                if ($isEmpty) {
                    continue;
                }

                if ($studentName === '' || $birthdateRaw === '' || $parentName === '' || $parentEmail === '') {
                    continue;
                }

                if (!filter_var($parentEmail, FILTER_VALIDATE_EMAIL)) {
                    continue;
                }

                $birthdate = $this->parseDate($birthdateRaw);
                if ($birthdate === null) {
                    continue;
                }

                $whatsappRaw = $this->getValue($row, $mappedHeaders, 'parent_whatsapp');
                $whatsapp = clean_phone($whatsappRaw);
                $dialCode = '+58';

                if ($whatsapp !== '') {
                    if (str_starts_with($whatsapp, '58') && strlen($whatsapp) > 10) {
                        $whatsapp = substr($whatsapp, 2);
                    }
                    if (str_starts_with($whatsapp, '0')) {
                        $whatsapp = substr($whatsapp, 1);
                    }
                } else {
                    $whatsapp = null;
                }

                $studentComment = $this->getValue($row, $mappedHeaders, 'student_comment');

                // Buscar o crear usuario con rol "Padre"
                $user = User::whereRaw('LOWER(email) = ?', [$parentEmail])->first();
                if (!$user) {
                    $user = User::create([
                        'name' => $parentName,
                        'email' => $parentEmail,
                        'dial_code' => $dialCode,
                        'whatsapp' => $whatsapp,
                        'password' => Hash::make(Str::random(14)),
                        'role' => 'Padre',
                    ]);
                    $createdParents++;
                }

                // Buscar o crear estudiante
                $student = Student::where('user_id', $user->id)
                    ->whereDate('birthdate', $birthdate)
                    ->whereRaw('LOWER(name) = ?', [mb_strtolower($studentName)])
                    ->first();

                if (!$student) {
                    $student = Student::create([
                        'user_id' => $user->id,
                        'name' => $studentName,
                        'birthdate' => $birthdate,
                        'comment' => $studentComment ?: null,
                        'active' => true,
                    ]);
                    $createdStudents++;
                }

                // Registrar inscripción si el status corresponde a inscripción activa
                $statusRaw = $this->getValue($row, $mappedHeaders, 'status');
                $statusNormalized = mb_strtolower(trim($statusRaw));

                if ($statusNormalized === 'inscripcion') {
                    $course = $this->findCourseForSheet($sheetName);
                    if ($course) {
                        $program = Program::where('slug', 'little-strikers')->first();
                        if ($program) {
                            $enrollment = Enrollment::firstOrCreate([
                                'student_id' => $student->id,
                                'program_id' => $program->id,
                                'parent_id' => $user->id,
                            ], [
                                'status' => 'active',
                                'payment_method' => 'manual',
                                'payment_status' => 'pending',
                                'terms_accepted' => true,
                                'image_consent_accepted' => true,
                            ]);

                            if ($enrollment->wasRecentlyCreated) {
                                $createdEnrollments++;
                            }

                            $enrollment->courses()->syncWithoutDetaching([$course->id]);
                        }
                    }
                }
            }
        }

        $this->command->info("Importación de todas las hojas completada. Total Padres creados: {$createdParents}. Total Estudiantes creados: {$createdStudents}. Total Inscripciones creadas: {$createdEnrollments}.");
    }

    private function buildHeaderMap(array $headerRow): array
    {
        $aliases = [
            'student_name' => ['student_name', 'nombre_participante', 'nombre_estudiante', 'nombre_del_estudiante', 'estudiante', 'alumno', 'nombre alumno', 'nombre'],
            'student_birthdate' => ['student_birthdate', 'birthdate', 'fecha_nacimiento', 'fecha_de_nacimiento', 'nacimiento', 'fecha nacimiento', 'fecha de nac', 'fecha_de_nac'],
            'student_comment' => ['student_comment', 'comment', 'comentario', 'comentarios', 'observaciones'],
            'parent_name' => ['parent_name', 'representante_nombre', 'nombre_representante', 'representante', 'padre_nombre', 'nombre_padre', 'nombre_representante_legal'],
            'parent_email' => ['parent_email', 'representante_email', 'email_representante', 'correo_representante', 'email', 'correo'],
            'parent_dial_code' => ['parent_dial_code', 'dial_code', 'codigo_pais', 'codigo', 'cod_pais'],
            'parent_whatsapp' => ['parent_whatsapp', 'representante_whatsapp', 'telefono_representante', 'whatsapp_representante', 'telefono', 'celular', 'whatsapp'],
            'status' => ['status', 'estado', 'estatus'],
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

    private function findCourseForSheet(string $sheetName): ?Course
    {
        $sheetName = trim($sheetName);
        
        $category = null;
        if (stripos($sheetName, 'Baby') !== false) {
            $category = 'Baby';
        } elseif (stripos($sheetName, 'Mini') !== false) {
            $category = 'Mini';
        } elseif (stripos($sheetName, 'Super') !== false) {
            $category = 'Super';
        }

        $day = null;
        if (stripos($sheetName, 'Lun') !== false) {
            $day = 'Lunes';
        } elseif (stripos($sheetName, 'Mier') !== false) {
            $day = 'Miércoles';
        } elseif (stripos($sheetName, 'Sab') !== false) {
            $day = 'Sábado';
        }

        if (!$category || !$day) {
            return null;
        }

        $expectedTitle = "Little Strikers {$category} - {$day}";

        $branch = Branch::where('name', 'SEDE SAN LUIS')->first();
        if (!$branch) {
            return null;
        }

        return Course::where('title', $expectedTitle)
            ->where('branch_id', $branch->id)
            ->first();
    }
}
