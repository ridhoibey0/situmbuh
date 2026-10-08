<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Role: superadmin, admin, user (orang tua), kader, nakes. String agar mudah ditambah.
        Schema::table('users', function (Blueprint $table) {
            $table->string('roles', 20)->default('user')->nullable()->change();
        });

        Schema::create('children', function (Blueprint $table) {
            $table->id();
            // Orang tua boleh kosong: anak dapat didaftarkan kader sebelum orang tua punya akun.
            $table->foreignId('parent_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('registered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('nik', 16)->nullable()->unique();
            $table->enum('gender', ['male', 'female']);
            $table->date('bod');
            $table->char('village_id', 10)->nullable();
            $table->foreign('village_id')->references('id')->on('villages')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // Kader/nakes yang bertanggung jawab atas seorang anak.
        Schema::create('child_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role', 20); // kader | nakes
            $table->timestamps();
            $table->unique(['child_id', 'user_id']);
        });

        Schema::table('user_measurements', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
            $table->foreignId('child_id')->nullable()->after('id')->constrained('children')->cascadeOnDelete();
            $table->foreignId('measured_by')->nullable()->after('measured_at')->constrained('users')->nullOnDelete();
        });

        Schema::table('kpsp_results', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
            $table->foreignId('child_id')->nullable()->after('id')->constrained('children')->cascadeOnDelete();
        });

        $this->backfillChildrenFromUsers();
    }

    /**
     * Setiap akun orang tua lama merepresentasikan satu anak (bod/gender ada di tabel users).
     * Pindahkan menjadi baris children dan hubungkan pengukuran serta hasil KPSP-nya.
     */
    private function backfillChildrenFromUsers(): void
    {
        DB::table('users')
            ->where('roles', 'user')
            ->whereNotNull('bod')
            ->whereNotNull('gender')
            ->orderBy('id')
            ->chunkById(200, function ($users) {
                foreach ($users as $user) {
                    $childId = DB::table('children')->insertGetId([
                        'parent_id' => $user->id,
                        'name' => $user->name,
                        'nik' => $user->nik,
                        'gender' => $user->gender,
                        'bod' => $user->bod,
                        'village_id' => $user->village_id,
                        'created_at' => $user->created_at,
                        'updated_at' => now(),
                    ]);

                    DB::table('user_measurements')->where('user_id', $user->id)->update(['child_id' => $childId]);
                    DB::table('kpsp_results')->where('user_id', $user->id)->update(['child_id' => $childId]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('kpsp_results', function (Blueprint $table) {
            $table->dropConstrainedForeignId('child_id');
        });

        Schema::table('user_measurements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('measured_by');
            $table->dropConstrainedForeignId('child_id');
        });

        Schema::dropIfExists('child_user');
        Schema::dropIfExists('children');
    }
};
