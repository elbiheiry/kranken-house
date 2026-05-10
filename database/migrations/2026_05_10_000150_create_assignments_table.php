<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('assignments', function (Blueprint $table) {
      $table->id();
      $table->foreignId('resident_id')->constrained()->cascadeOnDelete();
      $table->foreignId('procedure_id')->constrained()->cascadeOnDelete();
      $table->foreignId('recommended_by_id')->nullable()->constrained('users')->nullOnDelete();
      $table->decimal('priority_score', 5, 2)->default(0);
      $table->text('reason');
      $table->date('scheduled_for')->nullable();
      $table->string('status')->default('suggested');
      $table->timestamps();

      $table->index(['procedure_id', 'priority_score']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('assignments');
  }
};
