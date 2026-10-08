<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('follow_ups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained()->cascadeOnDelete();
            // Kondisi saat tindak lanjut dibuat (baseline) dan kondisi pada pemantauan setelah selesai (outcome).
            $table->foreignId('baseline_assessment_id')->nullable()->constrained('risk_assessments')->nullOnDelete();
            $table->foreignId('outcome_assessment_id')->nullable()->constrained('risk_assessments')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action_type', 30);
            $table->text('notes')->nullable();
            $table->date('due_date');
            $table->string('status', 15)->default('open'); // open | in_progress | done | cancelled
            $table->timestamp('completed_at')->nullable();
            $table->text('result_notes')->nullable();
            $table->timestamps();

            $table->index(['child_id', 'status']);
            $table->index(['assigned_to', 'status', 'due_date']);
        });

        Schema::create('follow_up_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('follow_up_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('from_status', 15)->nullable();
            $table->string('to_status', 15);
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('follow_up_logs');
        Schema::dropIfExists('follow_ups');
    }
};
