<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nik', 16)->unique()->nullable()->after('email'); // Kolom NIK, unik, opsional
            $table
                ->enum('gender', ['male', 'female'])
                ->nullable()
                ->after('nik'); // Kolom Gender
            $table->string('avatar')->nullable()->after('gender'); // Kolom Avatar
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('nik');
            $table->dropColumn('gender');
            $table->dropColumn('avatar');
        });
    }
};
