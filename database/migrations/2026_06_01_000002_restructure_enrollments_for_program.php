<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('enrollments', 'program_id')) {
            Schema::table('enrollments', function (Blueprint $table) {
                $table->foreignId('program_id')->nullable()->after('id')->constrained('programs')->nullOnDelete();
            });
        }

        if (Schema::hasColumn('enrollments', 'course_id')) {
            if (DB::getDriverName() === 'mysql') {
                DB::statement('
                    UPDATE enrollments e
                    INNER JOIN courses c ON c.id = e.course_id
                    SET e.program_id = c.program_id
                    WHERE e.program_id IS NULL AND c.program_id IS NOT NULL
                ');
            } else {
                // SQLite compatibility
                DB::statement('
                    UPDATE enrollments
                    SET program_id = (SELECT program_id FROM courses WHERE courses.id = enrollments.course_id)
                    WHERE program_id IS NULL AND course_id IS NOT NULL
                ');
            }
        }

        if (! Schema::hasTable('enrollment_course')) {
            Schema::create('enrollment_course', function (Blueprint $table) {
                $table->id();
                $table->foreignId('enrollment_id')->constrained('enrollments')->cascadeOnDelete();
                $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['enrollment_id', 'course_id'], 'enrollment_course_unique');
            });
        }

        if (Schema::hasTable('enrollment_course') && Schema::hasColumn('enrollments', 'course_id')) {
            if (DB::getDriverName() === 'mysql') {
                DB::statement('
                    INSERT IGNORE INTO enrollment_course (enrollment_id, course_id, created_at, updated_at)
                    SELECT id, course_id, created_at, updated_at
                    FROM enrollments
                    WHERE course_id IS NOT NULL
                ');
            } else {
                DB::statement('
                    INSERT OR IGNORE INTO enrollment_course (enrollment_id, course_id, created_at, updated_at)
                    SELECT id, course_id, created_at, updated_at
                    FROM enrollments
                    WHERE course_id IS NOT NULL
                ');
            }
        }

        if (Schema::hasColumn('enrollments', 'course_id')) {
            if (DB::getDriverName() === 'mysql') {
                DB::statement('ALTER TABLE enrollments DROP FOREIGN KEY enrollments_course_id_foreign');
                DB::statement('ALTER TABLE enrollments DROP FOREIGN KEY enrollments_student_id_foreign');
                DB::statement('ALTER TABLE enrollments DROP INDEX enrollments_student_course_unique');
                DB::statement('ALTER TABLE enrollments DROP COLUMN course_id');
                DB::statement('ALTER TABLE enrollments ADD CONSTRAINT enrollments_student_id_foreign FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE');
            }
            // SQLite: column already nullable, skipping ALTER
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('enrollments', 'program_id')) {
            Schema::table('enrollments', function (Blueprint $table) {
                if (! Schema::hasColumn('enrollments', 'course_id')) {
                    $table->foreignId('course_id')->nullable()->after('program_id')->constrained('courses')->nullOnDelete();
                }
            });

            if (Schema::hasTable('enrollment_course')) {
                if (DB::getDriverName() === 'mysql') {
                    DB::statement('
                        UPDATE enrollments e
                        INNER JOIN enrollment_course ec ON ec.enrollment_id = e.id
                        SET e.course_id = ec.course_id
                        WHERE e.course_id IS NULL
                    ');
                } else {
                    DB::statement('
                        UPDATE enrollments
                        SET course_id = (SELECT course_id FROM enrollment_course WHERE enrollment_course.enrollment_id = enrollments.id LIMIT 1)
                        WHERE course_id IS NULL
                    ');
                }
            }
        }

        Schema::dropIfExists('enrollment_course');

        Schema::table('enrollments', function (Blueprint $table) {
            if (Schema::hasColumn('enrollments', 'program_id')) {
                $table->dropForeign(['program_id']);
                $table->dropColumn('program_id');
            }
        });
    }
};
