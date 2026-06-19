<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Resident;
use App\Models\User;
use App\Models\UserRoleOption;
use App\Support\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserManagementController extends Controller
{
  public function index(): View
  {
    return view('admin.users.index', [
      'users' => User::query()->latest('id')->paginate(20),
    ]);
  }

  public function create(): View
  {
    return view('admin.users.form', [
      'user' => new User(),
      'roles' => UserRoleOption::query()->orderBy('label')->get(),
      'isEdit' => false,
    ]);
  }

  public function store(Request $request, NotificationService $notificationService): RedirectResponse
  {
    $roleCodes = UserRoleOption::query()->pluck('code')->all();

    $validated = $request->validate([
      'name' => ['required', 'string', 'max:255'],
      'email' => ['required', 'email', 'max:255', 'unique:users,email'],
      'role' => ['required', Rule::in($roleCodes)],
      'password' => ['required', 'string', 'min:8'],
      'training_year' => ['nullable', 'integer', 'min:1', 'max:6'],
    ]);

    $user = User::query()->create([
      'name' => $validated['name'],
      'email' => $validated['email'],
      'role' => $validated['role'],
      'password' => Hash::make($validated['password']),
    ]);

    $this->syncResidentProfile($user, $validated);

    $notificationService->notifyAllExcept(
      $request->user()->id,
      'admin-user-created',
      'User created',
      sprintf('%s created a new user account: %s (%s).', $request->user()->name, $user->name, $user->role),
      ['user_id' => $user->id]
    );

    return redirect()->route('admin.users.index')->with('status', 'User created successfully.');
  }

  public function edit(User $user): View
  {
    return view('admin.users.form', [
      'user' => $user,
      'roles' => UserRoleOption::query()->orderBy('label')->get(),
      'isEdit' => true,
    ]);
  }

  public function update(Request $request, User $user, NotificationService $notificationService): RedirectResponse
  {
    $roleCodes = UserRoleOption::query()->pluck('code')->all();

    $validated = $request->validate([
      'name' => ['required', 'string', 'max:255'],
      'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
      'role' => ['required', Rule::in($roleCodes)],
      'password' => ['nullable', 'string', 'min:8'],
      'training_year' => ['nullable', 'integer', 'min:1', 'max:6'],
    ]);

    $payload = [
      'name' => $validated['name'],
      'email' => $validated['email'],
      'role' => $validated['role'],
    ];

    if (! empty($validated['password'])) {
      $payload['password'] = Hash::make($validated['password']);
    }

    $user->update($payload);
    $this->syncResidentProfile($user, $validated);

    $notificationService->notifyAllExcept(
      $request->user()->id,
      'admin-user-updated',
      'User updated',
      sprintf('%s updated user account: %s (%s).', $request->user()->name, $user->name, $user->role),
      ['user_id' => $user->id]
    );

    return redirect()->route('admin.users.index')->with('status', 'User updated successfully.');
  }

  public function destroy(Request $request, User $user, NotificationService $notificationService): RedirectResponse
  {
    if ((int) $request->user()->id === (int) $user->id) {
      return back()->with('status', 'You cannot delete your own account.');
    }

    $userName = $user->name;
    $user->delete();

    $notificationService->notifyAllExcept(
      $request->user()->id,
      'admin-user-deleted',
      'User deleted',
      sprintf('%s deleted user account: %s.', $request->user()->name, $userName),
      ['name' => $userName]
    );

    return redirect()->route('admin.users.index')->with('status', 'User deleted successfully.');
  }

  private function syncResidentProfile(User $user, array $validated): void
  {
    if ($user->role !== 'resident') {
      $user->residentProfile()?->delete();

      return;
    }

    Resident::query()->updateOrCreate(
      ['user_id' => $user->id],
      ['training_year' => $validated['training_year'] ?? 1]
    );
  }
}
