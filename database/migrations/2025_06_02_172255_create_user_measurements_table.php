<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserMeasurementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_measurements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('weight', 5, 2)->nullable(); // Berat badan (kg)
            $table->decimal('height', 5, 2)->nullable(); // Tinggi badan (cm)
            $table->decimal('head_circumference', 5, 2)->nullable(); // Lingkar kepala (cm)
            $table->decimal('arm_circumference', 5, 2)->nullable(); // Lingkar lengan (cm)
            $table->timestamp('measured_at')->nullable(); // Waktu pengukuran
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_measurements');
    }
}
