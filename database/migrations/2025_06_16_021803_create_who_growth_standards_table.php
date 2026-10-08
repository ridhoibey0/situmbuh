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
        Schema::create('who_growth_standards', function (Blueprint $table) {
            $table->id();
            $table->enum('gender', ['male', 'female']); // L = Laki-laki, P = Perempuan
            $table->string('parameter'); // BB/U, TB/U, IMT/U, LK/U
            $table->integer('age_in_months'); // 0 - 60
            $table->float('sd_3_negatif');
            $table->float('sd_2_negatif');
            $table->float('sd_1_negatif');
            $table->float('sd_median');
            $table->float('sd_1_positif');
            $table->float('sd_2_positif');
            $table->float('sd_3_positif');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('who_growth_standards');
    }
};
