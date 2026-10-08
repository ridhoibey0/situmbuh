<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Nomor HP orang tua untuk anak yang didaftarkan kader sebelum orang tua punya akun.
        // Anak tertaut otomatis ketika akun dengan nomor tersebut dibuat.
        Schema::table('children', function (Blueprint $table) {
            $table->string('parent_phone', 20)->nullable()->after('parent_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('children', function (Blueprint $table) {
            $table->dropColumn('parent_phone');
        });
    }
};
