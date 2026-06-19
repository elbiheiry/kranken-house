<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CaseLog;
use App\Models\Procedure;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

class DashboardController extends Controller
{
  public function index(): View
  {
    return view('admin.dashboard', [
      'userCount' => User::query()->count('*'),
      'procedureCount' => Procedure::query()->count('*'),
      'caseLogCount' => CaseLog::query()->count('*'),
      'adminCount' => User::query()->where('role', 'administrator')->count('*'),
    ]);
  }

  public function sendMonthlyDigest(): RedirectResponse
  {
    Artisan::call('notifications:director-monthly-digest');

    return back()->with('status', 'Monthly digest has been sent to directors.');
  }
}
