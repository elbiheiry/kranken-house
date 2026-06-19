<?php

namespace App\Support;

use App\Models\SystemNotification;
use App\Models\User;

class NotificationService
{
  public function notifyAll(string $type, string $title, string $message, array $data = []): void
  {
    $this->notifyUsers(User::query()->pluck('id'), $type, $title, $message, $data);
  }

  public function notifyAllExcept(?int $excludedUserId, string $type, string $title, string $message, array $data = []): void
  {
    $query = User::query();

    if ($excludedUserId) {
      $query->where('id', '!=', $excludedUserId);
    }

    $this->notifyUsers($query->pluck('id'), $type, $title, $message, $data);
  }

  public function notifyByRoles(array $roles, string $type, string $title, string $message, array $data = []): void
  {
    $query = User::query();

    if (! empty($roles)) {
      $query->where(function ($builder) use ($roles) {
        foreach (array_values($roles) as $index => $role) {
          if ($index === 0) {
            $builder->where('role', $role);
          } else {
            $builder->orWhere('role', $role);
          }
        }
      });
    }

    $this->notifyUsers(
      $query->pluck('id'),
      $type,
      $title,
      $message,
      $data
    );
  }

  public function notifyUsers(iterable $userIds, string $type, string $title, string $message, array $data = []): void
  {
    $now = now();
    $uniqueIds = collect($userIds)
      ->filter(fn($id) => ! is_null($id))
      ->map(fn($id) => (int) $id)
      ->unique()
      ->values();

    if ($uniqueIds->isEmpty()) {
      return;
    }

    $rows = $uniqueIds->map(fn(int $userId) => [
      'user_id' => $userId,
      'type' => $type,
      'title' => $title,
      'message' => $message,
      'data' => json_encode($data),
      'read_at' => null,
      'created_at' => $now,
      'updated_at' => $now,
    ])->all();

    SystemNotification::query()->insert($rows);
  }
}
