<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
  public function poll(Request $request): JsonResponse
  {
    $user = $request->user();

    $items = $user->notifications()
      ->latest('id')
      ->take(12)
      ->get()
      ->map(fn($notification) => [
        'id' => $notification->id,
        'title' => $notification->title,
        'message' => $notification->message,
        'type' => $notification->type,
        'read_at' => optional($notification->read_at)?->toDateTimeString(),
        'created_at' => optional($notification->created_at)?->toDateTimeString(),
      ]);

    return response()->json([
      'unread_count' => $user->notifications()->whereNull('read_at')->count(),
      'items' => $items,
    ]);
  }

  public function markAllRead(Request $request): JsonResponse
  {
    $request->user()->notifications()->whereNull('read_at')->update([
      'read_at' => now(),
    ]);

    return response()->json(['ok' => true]);
  }
}
