<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordStrengthValidationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::create(['nama' => 'user', 'label' => 'Pemohon']);
        $this->user = User::create([
            'username' => 'testuser',
            'nama_lengkap' => 'Test User',
            'password' => Hash::make('OldPassword123!'),
            'role_id' => $role->id,
            'is_active' => true,
            'must_change_pwd' => true,
        ]);
    }

    public function test_force_change_rejects_weak_password()
    {
        // 1. Password kurang dari 8 karakter
        $response = $this->actingAs($this->user)
            ->post(route('password.force-change.submit'), [
                'password' => 'Ab1!',
                'password_confirmation' => 'Ab1!',
            ]);
        $response->assertSessionHasErrors('password');

        // 2. Tanpa angka & simbol (hanya huruf)
        $response = $this->actingAs($this->user)
            ->post(route('password.force-change.submit'), [
                'password' => 'Hanyahurufbesar',
                'password_confirmation' => 'Hanyahurufbesar',
            ]);
        $response->assertSessionHasErrors('password');

        // 3. Tanpa simbol
        $response = $this->actingAs($this->user)
            ->post(route('password.force-change.submit'), [
                'password' => 'Password1234',
                'password_confirmation' => 'Password1234',
            ]);
        $response->assertSessionHasErrors('password');

        // 4. Konfirmasi tidak cocok
        $response = $this->actingAs($this->user)
            ->post(route('password.force-change.submit'), [
                'password' => 'PasswordKuat123!',
                'password_confirmation' => 'BedaPassword123!',
            ]);
        $response->assertSessionHasErrors('password');
    }

    public function test_force_change_accepts_strong_password()
    {
        $response = $this->actingAs($this->user)
            ->post(route('password.force-change.submit'), [
                'password' => 'PasswordKuat123!',
                'password_confirmation' => 'PasswordKuat123!',
            ]);

        $response->assertRedirect(route('dashboard'));
        $this->user->refresh();
        $this->assertFalse($this->user->must_change_pwd);
        $this->assertTrue(Hash::check('PasswordKuat123!', $this->user->password));
    }

    public function test_profile_change_password_rejects_weak_and_accepts_strong()
    {
        // Reject weak password
        $response = $this->actingAs($this->user)
            ->post(route('profile.update-password'), [
                'password_lama' => 'OldPassword123!',
                'password_baru' => 'weakpassword',
                'password_baru_confirmation' => 'weakpassword',
            ]);
        $response->assertSessionHasErrors('password_baru');

        // Accept strong password
        $response = $this->actingAs($this->user)
            ->post(route('profile.update-password'), [
                'password_lama' => 'OldPassword123!',
                'password_baru' => 'NewStrongPass99#',
                'password_baru_confirmation' => 'NewStrongPass99#',
            ]);

        $response->assertSessionHasNoErrors();
        $this->user->refresh();
        $this->assertTrue(Hash::check('NewStrongPass99#', $this->user->password));
    }
}
