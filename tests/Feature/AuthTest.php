<?php

namespace Tests\Feature;

use App\Models\Hotel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login_from_root(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');
    }

    public function test_guests_are_redirected_to_login_from_dashboard(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Daily Reporting System');
    }

    public function test_users_can_authenticate_using_livewire(): void
    {
        $hotel = Hotel::create([
            'name' => 'Komaneka at Bisma',
            'code' => 'KB',
            'is_active' => true,
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'user@hotel.com',
            'password' => bcrypt('password'),
            'role' => 'user',
            'hotel_id' => $hotel->id,
        ]);

        Livewire::test(\App\Livewire\Auth\Login::class)
            ->set('email', 'user@hotel.com')
            ->set('password', 'password')
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_users_cannot_authenticate_with_invalid_password(): void
    {
        $hotel = Hotel::create([
            'name' => 'Komaneka at Bisma',
            'code' => 'KB',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Test User',
            'email' => 'user@hotel.com',
            'password' => bcrypt('password'),
            'role' => 'user',
            'hotel_id' => $hotel->id,
        ]);

        Livewire::test(\App\Livewire\Auth\Login::class)
            ->set('email', 'user@hotel.com')
            ->set('password', 'wrong-password')
            ->call('login')
            ->assertHasErrors(['email']);

        $this->assertGuest();
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }
}
