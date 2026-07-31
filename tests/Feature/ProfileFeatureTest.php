<?php

namespace Tests\Feature;

use App\Models\Resident;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileFeatureTest extends TestCase
{
  use RefreshDatabase;

  public function test_resident_can_update_own_profile_and_training_year(): void
  {
    $user = User::factory()->create([
      'name' => 'Resident One',
      'email' => 'resident@example.com',
      'role' => 'resident',
      'password' => Hash::make('password'),
    ]);

    Resident::create([
      'user_id' => $user->id,
      'training_year' => 2,
    ]);

    $this->actingAs($user);

    $response = $this->patch(route('profile.update'), [
      'name' => 'Resident Updated',
      'email' => 'resident-updated@example.com',
      'password' => 'new-password',
      'training_year' => 4,
    ]);

    $response->assertRedirect(route('profile.edit'));

    $this->assertDatabaseHas('users', [
      'id' => $user->id,
      'name' => 'Resident Updated',
      'email' => 'resident-updated@example.com',
    ]);

    $this->assertDatabaseHas('residents', [
      'user_id' => $user->id,
      'training_year' => 4,
    ]);

    $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
  }

  public function test_non_resident_can_update_own_profile_without_creating_resident_record(): void
  {
    $user = User::factory()->create([
      'name' => 'Supervisor One',
      'email' => 'supervisor@example.com',
      'role' => 'supervisor',
      'password' => Hash::make('password'),
    ]);

    $this->actingAs($user);

    $response = $this->patch(route('profile.update'), [
      'name' => 'Supervisor Updated',
      'email' => 'supervisor-updated@example.com',
      'password' => 'new-password',
      'training_year' => 5,
    ]);

    $response->assertRedirect(route('profile.edit'));

    $this->assertDatabaseHas('users', [
      'id' => $user->id,
      'name' => 'Supervisor Updated',
      'email' => 'supervisor-updated@example.com',
    ]);

    $this->assertDatabaseMissing('residents', [
      'user_id' => $user->id,
    ]);

    $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
  }
}
