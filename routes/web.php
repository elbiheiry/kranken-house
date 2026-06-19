<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ProcedureManagementController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Director\DashboardController;
use App\Http\Controllers\Director\RecommendationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Resident\DashboardController as ResidentDashboardController;
use App\Http\Controllers\Resident\CaseLogController;
use App\Http\Controllers\Supervisor\DashboardController as SupervisorDashboardController;
use App\Http\Controllers\Supervisor\ApprovalController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
  Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
  Route::post('/login', [AuthController::class, 'login'])->name('login.perform');
});

Route::get('/locale/{lang}', function (string $lang) {
  if (in_array($lang, ['en', 'de'])) {
    session(['locale' => $lang]);
  }
  return back();
})->name('locale.switch');

Route::middleware('auth')->group(function () {
  Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

  Route::get('/', function () {
    $role = Auth::user()->role;

    if ($role === 'resident') {
      return redirect()->route('resident.dashboard');
    }

    if ($role === 'supervisor') {
      return redirect()->route('supervisor.dashboard');
    }

    if ($role === 'director') {
      return redirect()->route('director.dashboard');
    }

    if ($role === 'administrator') {
      return redirect()->route('admin.dashboard');
    }

    abort(403);
  })->name('home');

  Route::get('/notifications/poll', [NotificationController::class, 'poll'])->name('notifications.poll');
  Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');

  Route::prefix('resident')->name('resident.')->middleware('role:resident')->group(function () {
    Route::get('/dashboard', [ResidentDashboardController::class, 'index'])->name('dashboard');
    Route::get('/peers-progress', [ResidentDashboardController::class, 'peersProgress'])->name('peers-progress');
    Route::get('/case-logs', [CaseLogController::class, 'index'])->name('case-logs.index');
    Route::get('/case-logs/create', [CaseLogController::class, 'create'])->name('case-logs.create');
    Route::post('/case-logs', [CaseLogController::class, 'store'])->name('case-logs.store');
  });

  Route::prefix('supervisor')->name('supervisor.')->middleware('role:supervisor')->group(function () {
    Route::get('/dashboard', [SupervisorDashboardController::class, 'index'])->name('dashboard');
    Route::get('/approvals', [ApprovalController::class, 'index'])->name('approvals.index');
    Route::patch('/approvals/{approval}', [ApprovalController::class, 'update'])->name('approvals.update');
  });

  Route::prefix('director')->name('director.')->middleware('role:director')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/residents-progress', [DashboardController::class, 'residentsProgress'])->name('residents-progress');
    Route::post('/recommendations', [RecommendationController::class, 'store'])->name('recommendations.store');
  });

  Route::prefix('admin')->name('admin.')->middleware('role:administrator')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/send-monthly-digest', [AdminDashboardController::class, 'sendMonthlyDigest'])->name('dashboard.send-monthly-digest');
    Route::resource('users', UserManagementController::class)->except(['show']);
    Route::resource('procedures', ProcedureManagementController::class)->except(['show']);
  });
});
