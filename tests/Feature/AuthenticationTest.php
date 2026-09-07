<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test login page is accessible
     */
    public function test_login_page_is_accessible()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }

    /**
     * Test successful login redirects to dashboard
     */
    public function test_user_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin_qa',
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Test login fails with invalid credentials
     */
    public function test_user_cannot_login_with_invalid_credentials()
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'invalid-password',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    /**
     * Test logout functionality
     */
    public function test_user_can_logout()
    {
        $user = User::factory()->create([
            'role' => 'admin_qa',
        ]);

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    /**
     * Test unauthenticated user is redirected to login
     */
    public function test_unauthenticated_user_is_redirected_to_login()
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    /**
     * Test home page redirects to login when not authenticated
     */
    public function test_home_page_redirects_to_login_when_not_authenticated()
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');
    }

    /**
     * Test home page redirects to dashboard when authenticated
     */
    public function test_home_page_redirects_to_dashboard_when_authenticated()
    {
        $user = User::factory()->create(['role' => 'admin_qa']);
        $response = $this->actingAs($user)->get('/');
        $response->assertRedirect('/dashboard');
    }
}
