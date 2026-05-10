<?php

namespace App\Support;

use App\Models\Procedure;
use App\Models\Resident;
use App\Models\TrainingRequirement;

class ProgressCalculator
{
  public static function expectedByTrainingYear(Resident $resident, TrainingRequirement $requirement): int
  {
    $year = max(1, min(6, $resident->training_year));

    if ($year <= 3) {
      return (int) round(($requirement->expected_by_r3 / 3) * $year);
    }

    $remainingYears = 3;
    $yearsAfterR3 = $year - 3;
    $remainingCases = $requirement->required_by_end_of_program - $requirement->expected_by_r3;

    return (int) round($requirement->expected_by_r3 + (($remainingCases / $remainingYears) * $yearsAfterR3));
  }

  public static function status(float $completionRatio): string
  {
    if ($completionRatio >= 1.0) {
      return 'green';
    }

    if ($completionRatio >= 0.7) {
      return 'yellow';
    }

    return 'red';
  }

  public static function statusLabel(string $status): string
  {
    return match ($status) {
      'green' => 'On track',
      'yellow' => 'At risk',
      default => 'Behind',
    };
  }

  public static function completionRatio(int $completed, int $expected): float
  {
    if ($expected <= 0) {
      return $completed > 0 ? 1.0 : 0.0;
    }

    return $completed / $expected;
  }

  public static function completedCount(Resident $resident, Procedure $procedure): int
  {
    return $resident->caseLogs()
      ->where('procedure_id', $procedure->id)
      ->whereIn('role', ['first_assistant', 'primary', 'supervised_primary'])
      ->whereHas('approval', fn($query) => $query->where('status', 'approved'))
      ->count();
  }
}
