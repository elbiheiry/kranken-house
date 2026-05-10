<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('case_approvals', function (Blueprint $table) {
      $table->id();
      $table->foreignId('case_log_id')->unique()->constrained()->cascadeOnDelete();
      $table->foreignId('supervisor_id')->constrained('users')->cascadeOnDelete();
      $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
      $table->text('feedback')->nullable();
      $table->enum('approved_role', ['assistant', 'first_assistant', 'primary', 'supervised_primary'])->nullable();
      $table->foreignId('approved_procedure_id')->nullable()->constrained('procedures')->nullOnDelete();
      $table->timestamp('decided_at')->nullable();
      $table->timestamps();

      $table->index('status');
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('case_approvals');
  }
};
