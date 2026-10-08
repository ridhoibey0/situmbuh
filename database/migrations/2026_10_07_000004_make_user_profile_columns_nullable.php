<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pendaftaran akun hanya meminta nama, nomor HP, dan kata sandi. Nama orang tua dilengkapi
     * di profil, sedangkan tanggal lahir kini milik tabel children. Kolom lama wajib terisi sehingga
     * pendaftaran gagal pada instalasi baru.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('parent_name')->nullable()->change();
            $table->date('bod')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Tidak dikembalikan menjadi NOT NULL: baris tanpa nilai akan membuat rollback gagal.
    }
};
