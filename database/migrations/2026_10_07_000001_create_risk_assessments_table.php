<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('risk_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained()->cascadeOnDelete();
            $table->foreignId('measurement_id')->nullable()->constrained('user_measurements')->nullOnDelete();
            $table->unsignedSmallInteger('score');
            $table->string('level', 10); // rendah | sedang | tinggi
            $table->json('factors'); // [{code, label, detail, points}]
            $table->decimal('z_tb', 5, 2)->nullable();
            $table->decimal('z_bb', 5, 2)->nullable();
            $table->timestamp('computed_at');
            $table->timestamps();

            $table->index(['child_id', 'computed_at']);
            $table->index(['level', 'score']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('risk_assessments');
    }
};
