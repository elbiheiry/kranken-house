<?php

namespace App\Http\Controllers;

use App\Models\Resident;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
  public function edit(Request $request): View
  {
    return view('profile.edit', [
      'user' => $request->user()->loadMissing('residentProfile'),
    ]);
  }

  public function update(Request $request): RedirectResponse
  {
    /** @var User $user */
    $user = $request->user();

    $validated = $request->validate([
      'name' => ['required', 'string', 'max:255'],
      'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
      'password' => ['nullable', 'string', 'min:8'],
      'training_year' => ['nullable', 'integer', 'min:1', 'max:6'],
    ]);

    $payload = [
      'name' => $validated['name'],
      'email' => $validated['email'],
    ];

    if (! empty($validated['password'])) {
      $payload['password'] = Hash::make($validated['password']);
    }

    $user->update($payload);

    if ($user->role === 'resident') {
      Resident::query()->updateOrCreate(
        ['user_id' => $user->id],
        ['training_year' => $validated['training_year'] ?? 1]
      );
    }

    return redirect()->route('profile.edit')->with('status', 'Profile updated successfully.');
  }
}
