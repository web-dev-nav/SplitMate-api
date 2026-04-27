<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthGoogleAndProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_update_email_via_api(): void
    {
        $user = User::factory()->create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'email' => 'old@example.com',
            'email_verified_at' => now(),
            'google_id' => null,
        ]);

        Sanctum::actingAs($user);

        $response = $this->patchJson('/api/v1/auth/me', [
            'email' => 'new@example.com',
        ]);

        $response->assertOk()
            ->assertJsonPath('user.email', 'new@example.com')
            ->assertJsonPath('user.email_verified_at', null);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'new@example.com',
        ]);
    }

    public function test_password_login_is_blocked_for_google_linked_account(): void
    {
        $user = User::factory()->create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'email' => 'google-user@example.com',
            'password' => Hash::make('secret123'),
            'google_id' => 'google-sub-123',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'secret123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        $this->assertStringContainsString(
            'Please sign in with Google',
            (string) data_get($response->json(), 'errors.email.0', '')
        );
    }

    public function test_google_login_updates_existing_user_matched_by_email(): void
    {
        $user = User::factory()->create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'name' => 'Old Name',
            'email' => 'match@example.com',
            'google_id' => null,
            'email_verified_at' => null,
            'password' => Hash::make('secret123'),
        ]);

        Http::fake([
            'https://oauth2.googleapis.com/tokeninfo*' => Http::response([
                'sub' => 'google-sub-999',
                'email' => 'match@example.com',
                'name' => 'Google Fresh Name',
                'email_verified' => 'true',
            ], 200),
        ]);

        $response = $this->postJson('/api/v1/auth/google', [
            'id_token' => 'fake-token',
        ]);

        $response->assertOk()
            ->assertJsonPath('user.email', 'match@example.com')
            ->assertJsonPath('user.name', 'Google Fresh Name')
            ->assertJsonPath('user.is_google_account', true)
            ->assertJsonPath('user.id', $user->uuid);

        $user->refresh();

        $this->assertSame('google-sub-999', $user->google_id);
        $this->assertSame('Google Fresh Name', $user->name);
        $this->assertNotNull($user->email_verified_at);
    }
}
