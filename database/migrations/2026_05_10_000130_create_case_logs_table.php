<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('case_logs', function (Blueprint $table) {
      $table->id();
      $table->foreignId('resident_id')->constrained()->cascadeOnDelete();
      $table->foreignId('procedure_id')->constrained()->cascadeOnDelete();
      $table->string('case_code');
      $table->enum('operation_type', ['emergency', 'elective']);
      $table->unsignedTinyInteger('difficulty_level');
      $table->enum('role', ['assistant', 'first_assistant', 'primary', 'supervised_primary']);
      $table->date('operation_date');
      $table->foreignId('supervisor_id')->nullable()->constrained('users')->nullOnDelete();
      $table->text('note')->nullable();
      $table->timestamps();

      $table->index(['resident_id', 'procedure_id']);
      $table->index('operation_date');
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('case_logs');
  }
};
