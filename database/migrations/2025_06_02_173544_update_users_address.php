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
            $table->char('province_id', 2)->nullable();
            $table->char('regency_id', 4)->nullable()->after('province_id'); // sesuaikan panjang char sesuai tabel regencies
            $table->char('district_id', 7)->nullable()->after('regency_id'); // sesuaikan
            $table->char('village_id', 10)->nullable()->after('district_id'); // sesuaikan

            $table->foreign('province_id')->references('id')->on('provinces')->onDelete('set null');
            $table->foreign('regency_id')->references('id')->on('regencies')->onDelete('set null');
            $table->foreign('district_id')->references('id')->on('districts')->onDelete('set null');
            $table->foreign('village_id')->references('id')->on('villages')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['province_id']);
            $table->dropColumn('province_id');

            $table->dropForeign(['regency_id']);
            $table->dropColumn('regency_id');

            $table->dropForeign(['district_id']);
            $table->dropColumn('district_id');

            $table->dropForeign(['village_id']);
            $table->dropColumn('village_id');
        });
    }
};
