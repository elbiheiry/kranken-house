<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
  private const EMAIL_DOMAIN = 'stmscaselog.com';

  public function showLogin(): View
  {
    return view('auth.login');
  }

  public function login(Request $request): RedirectResponse
  {
    $credentials = $request->validate([
      'email' => ['required', 'string', 'max:255'],
      'password' => ['required', 'string'],
    ]);

    $credentials['email'] = $this->normalizeEmailInput($credentials['email']);

    validator(
      ['email' => $credentials['email']],
      ['email' => ['required', 'email']]
    )->validate();

    if (! Auth::attempt($credentials, $request->boolean('remember'))) {
      return back()->withErrors([
        'email' => 'Invalid credentials.',
      ])->onlyInput('email');
    }

    $request->session()->regenerate();

    return redirect()->route('home');
  }

  public function logout(Request $request): RedirectResponse
  {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('login');
  }

  private function normalizeEmailInput(string $value): string
  {
    $normalized = strtolower(trim($value));
    $localPart = trim(strtok($normalized, '@') ?: '');

    return $localPart . '@' . self::EMAIL_DOMAIN;
  }
}
