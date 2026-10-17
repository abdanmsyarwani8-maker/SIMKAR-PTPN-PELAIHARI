<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_user_successfully_with_checkbox_state(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'role' => 'user',
            'is_active' => true,
        ]);

        // Simulasikan submit form update pengguna dari browser (is_active = "1")
        $response = $this->actingAs($admin)->put(route('users.update', $user), [
            'name' => 'John Doe Updated',
            'email' => 'john.updated@example.com',
            'role' => 'user',
            'is_active' => '1',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'John Doe Updated',
            'email' => 'john.updated@example.com',
            'is_active' => true,
        ]);

        // Simulasikan submit checkbox yang mengirim "on" (default value browser tanpa atribut value)
        $response2 = $this->actingAs($admin)->put(route('users.update', $user), [
            'name' => 'John Doe On',
            'email' => 'john.on@example.com',
            'role' => 'user',
            'is_active' => 'on',
        ]);

        $response2->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'John Doe On',
            'is_active' => true,
        ]);

        // Simulasikan submit form saat checkbox di-uncheck (is_active = "0")
        $response3 = $this->actingAs($admin)->put(route('users.update', $user), [
            'name' => 'John Doe Inactive',
            'email' => 'john.inactive@example.com',
            'role' => 'user',
            'is_active' => '0',
        ]);

        $response3->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'is_active' => false,
        ]);
    }

    public function test_admin_can_update_user_password(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user', 'password' => Hash::make('OldPassword123')]);

        $response = $this->actingAs($admin)->put(route('users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'role' => 'user',
            'is_active' => '1',
            'password' => 'NewSecretPassword123',
            'password_confirmation' => 'NewSecretPassword123',
        ]);

        $response->assertSessionHasNoErrors();
        $user->refresh();
        $this->assertTrue(Hash::check('NewSecretPassword123', $user->password));
    }

    public function test_admin_receives_clear_error_when_password_mismatches_or_is_invalid(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        // 1. Password mismatch
        $response = $this->actingAs($admin)->put(route('users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'role' => 'user',
            'is_active' => '1',
            'password' => 'Password123',
            'password_confirmation' => 'Beda12345',
        ]);

        $response->assertSessionHasErrors(['password']);
        $this->assertEquals(
            'Konfirmasi kata sandi tidak cocok dengan kata sandi baru. Pastikan kedua kolom kata sandi terisi sama persis.',
            session('errors')->first('password')
        );

        // 2. Password too short (< 8 chars)
        $response2 = $this->actingAs($admin)->put(route('users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'is_active' => '1',
            'password' => 'Pass1',
            'password_confirmation' => 'Pass1',
        ]);

        $response2->assertSessionHasErrors(['password']);
        $this->assertEquals(
            'Kata sandi baru terlalu pendek. Minimal harus 8 karakter.',
            session('errors')->first('password')
        );
    }

    public function test_profile_password_update_validates_with_helpful_messages(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('CurrentPassword123'),
        ]);

        // Wrong current password
        $response = $this->actingAs($user)->put(route('password.update'), [
            'current_password' => 'WrongPassword123',
            'password' => 'NewPassword123',
            'password_confirmation' => 'NewPassword123',
        ]);

        $response->assertSessionHasErrorsIn('updatePassword', ['current_password']);

        // Password mismatch
        $response2 = $this->actingAs($user)->put(route('password.update'), [
            'current_password' => 'CurrentPassword123',
            'password' => 'NewPassword123',
            'password_confirmation' => 'DifferentPassword123',
        ]);

        $response2->assertSessionHasErrorsIn('updatePassword', ['password']);
        $this->assertEquals(
            'Konfirmasi kata sandi baru tidak cocok. Pastikan isian kolom kata sandi baru dan ulangi kata sandi terisi sama persis.',
            session('errors')->getBag('updatePassword')->first('password')
        );
    }
}
