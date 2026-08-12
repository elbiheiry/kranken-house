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
  private const EMAIL_DOMAIN = 'stmscaselog.com';

  public function edit(Request $request): View
  {
    /** @var User $user */
    $user = $request->user()->loadMissing('residentProfile');

    return view('profile.edit', [
      'user' => $user,
      'emailUsername' => $this->extractEmailLocalPart($user->email),
    ]);
  }

  public function update(Request $request): RedirectResponse
  {
    /** @var User $user */
    $user = $request->user();

    $validated = $request->validate([
      'name' => ['required', 'string', 'max:255'],
      'email' => ['required', 'string', 'max:255'],
      'password' => ['nullable', 'string', 'min:8'],
      'training_year' => ['nullable', 'integer', 'min:1', 'max:6'],
    ]);

    $email = $this->normalizeEmailInput($validated['email']);

    validator(
      ['email' => $email],
      ['email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)]]
    )->validate();

    $payload = [
      'name' => $validated['name'],
      'email' => $email,
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

  private function normalizeEmailInput(string $value): string
  {
    $normalized = strtolower(trim($value));
    $localPart = trim(strtok($normalized, '@') ?: '');

    return $localPart . '@' . self::EMAIL_DOMAIN;
  }

  private function extractEmailLocalPart(string $email): string
  {
    return trim(strtok(strtolower($email), '@') ?: $email);
  }
}
