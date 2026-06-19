<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('user_roles', function (Blueprint $table) {
      $table->id();
      $table->string('code')->unique();
      $table->string('label');
      $table->boolean('is_system')->default(false);
      $table->timestamps();
    });

    Schema::create('operation_types', function (Blueprint $table) {
      $table->id();
      $table->string('code')->unique();
      $table->string('label');
      $table->timestamps();
    });

    Schema::create('operation_roles', function (Blueprint $table) {
      $table->id();
      $table->string('code')->unique();
      $table->string('label');
      $table->boolean('counts_towards_progress')->default(false);
      $table->timestamps();
    });

    Schema::create('approval_statuses', function (Blueprint $table) {
      $table->id();
      $table->string('code')->unique();
      $table->string('label');
      $table->timestamps();
    });

    Schema::create('procedure_yearly_targets', function (Blueprint $table) {
      $table->id();
      $table->foreignId('procedure_id')->constrained()->cascadeOnDelete();
      $table->unsignedTinyInteger('training_year');
      $table->unsignedInteger('required_cases')->default(0);
      $table->timestamps();

      $table->unique(['procedure_id', 'training_year']);
      $table->index('training_year');
    });

    $this->alterDynamicColumnsToString();
  }

  public function down(): void
  {
    $this->revertColumnsToEnums();

    Schema::dropIfExists('procedure_yearly_targets');
    Schema::dropIfExists('approval_statuses');
    Schema::dropIfExists('operation_roles');
    Schema::dropIfExists('operation_types');
    Schema::dropIfExists('user_roles');
  }

  private function alterDynamicColumnsToString(): void
  {
    $driver = DB::getDriverName();

    if (in_array($driver, ['mysql', 'mariadb'], true)) {
      DB::statement("ALTER TABLE users MODIFY role VARCHAR(50) NOT NULL DEFAULT 'resident'");
      DB::statement("ALTER TABLE case_logs MODIFY operation_type VARCHAR(50) NOT NULL");
      DB::statement("ALTER TABLE case_logs MODIFY role VARCHAR(50) NOT NULL");
      DB::statement("ALTER TABLE case_approvals MODIFY status VARCHAR(50) NOT NULL DEFAULT 'pending'");
      DB::statement("ALTER TABLE case_approvals MODIFY approved_role VARCHAR(50) NULL");
    }
  }

  private function revertColumnsToEnums(): void
  {
    $driver = DB::getDriverName();

    if (in_array($driver, ['mysql', 'mariadb'], true)) {
      DB::statement("ALTER TABLE users MODIFY role ENUM('resident','supervisor','director','administrator') NOT NULL DEFAULT 'resident'");
      DB::statement("ALTER TABLE case_logs MODIFY operation_type ENUM('emergency','elective') NOT NULL");
      DB::statement("ALTER TABLE case_logs MODIFY role ENUM('assistant','first_assistant','primary','supervised_primary') NOT NULL");
      DB::statement("ALTER TABLE case_approvals MODIFY status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending'");
      DB::statement("ALTER TABLE case_approvals MODIFY approved_role ENUM('assistant','first_assistant','primary','supervised_primary') NULL");
    }
  }
};
