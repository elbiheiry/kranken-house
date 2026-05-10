<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('training_requirements', function (Blueprint $table) {
      $table->id();
      $table->foreignId('procedure_id')->unique()->constrained()->cascadeOnDelete();
      $table->unsignedInteger('required_by_end_of_program');
      $table->unsignedInteger('expected_by_r3');
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('training_requirements');
  }
};
