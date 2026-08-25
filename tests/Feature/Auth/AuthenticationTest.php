<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_home_page_includes_role_login_dialog(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('guest-login-modal', false);
        $response->assertSee('role="dialog"', false);
        $response->assertSee('aria-modal="true"', false);
        $response->assertSee('aria-labelledby="modal-title"', false);
        $response->assertSee('id="modal-title"', false);
        $response->assertSee('id="modal-description"', false);
        $response->assertSee('aria-required="true"', false);
        $response->assertSee('Close modal');
        $response->assertSee('Log in to an existing account');
        $response->assertSee('name="role"', false);
    }

    public function test_failed_login_from_home_reopens_on_home(): void
    {
        $user = User::factory()->create();

        $response = $this->from('/')->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
            'role' => 'admin',
        ]);

        $this->assertGuest();
        $response->assertRedirect('/');
        $response->assertSessionHasErrors('credentials');

        $this->get('/')
            ->assertOk()
            ->assertSee('Invalid username or password. Please try again.')
            ->assertSee('guest-auth-banner', false);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect(route('home', absolute: false));
    }
}
