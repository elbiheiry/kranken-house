<?php

namespace App\Console\Commands;

use App\Models\CaseApproval;
use App\Models\Resident;
use App\Models\User;
use App\Support\NotificationService;
use App\Support\ProgressCalculator;
use Illuminate\Console\Command;

class SendDirectorMonthlyDigest extends Command
{
  protected $signature = 'notifications:director-monthly-digest';

  protected $description = 'Send monthly resident progress and approvals digest to directors';

  public function handle(NotificationService $notificationService): int
  {
    $directorIds = User::query()->where('role', 'director')->pluck('id')->all();

    if (empty($directorIds)) {
      $this->info('No directors found.');

      return self::SUCCESS;
    }

    $from = now()->subDays(30);

    $totalApproved = CaseApproval::query()
      ->where('status', 'approved')
      ->where('decided_at', '>=', $from)
      ->count();

    $totalRejected = CaseApproval::query()
      ->where('status', 'rejected')
      ->where('decided_at', '>=', $from)
      ->count();

    $residents = Resident::query()->with(['user', 'caseLogs.approval', 'caseLogs.procedure.trainingRequirement', 'caseLogs.procedure.yearlyTargets'])->get();

    $behindResidents = 0;

    foreach ($residents as $resident) {
      $completed = 0;
      $expected = 0;

      $procedures = $resident->caseLogs
        ->pluck('procedure')
        ->filter()
        ->unique('id')
        ->values();

      foreach ($procedures as $procedure) {
        if (! $procedure->trainingRequirement) {
          continue;
        }

        $completed += ProgressCalculator::completedCount($resident, $procedure);
        $expected += ProgressCalculator::expectedByTrainingYear($resident, $procedure->trainingRequirement);
      }

      $ratio = ProgressCalculator::completionRatio($completed, $expected);
      $status = ProgressCalculator::status($ratio);

      if ($status !== 'green') {
        $behindResidents++;
      }
    }

    $message = sprintf(
      '30-day digest: %d approved, %d rejected, %d residents at risk/behind.',
      $totalApproved,
      $totalRejected,
      $behindResidents
    );

    $notificationService->notifyUsers(
      $directorIds,
      'director-monthly-digest',
      'Monthly Training Digest',
      $message,
      [
        'period_days' => 30,
        'approved' => $totalApproved,
        'rejected' => $totalRejected,
        'at_risk_or_behind' => $behindResidents,
      ]
    );

    $this->info('Monthly digest sent to directors.');

    return self::SUCCESS;
  }
}
