<?php

namespace Tests\Feature;

use App\Models\Hotel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_dashboard(): void
    {
        $hotel = Hotel::create([
            'name' => 'Komaneka at Bisma',
            'code' => 'KB',
            'is_active' => true,
        ]);

        $user = User::create([
            'name' => 'Report Officer',
            'email' => 'officer@hotel.com',
            'password' => bcrypt('password'),
            'role' => 'user',
            'hotel_id' => $hotel->id,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Operations Dashboard');
        $response->assertSee('Komaneka at Bisma');
        $response->assertSee("Create Today's Report", false);
    }

    public function test_dashboard_displays_counts_and_recent_reports(): void
    {
        $hotel = Hotel::create([
            'name' => 'Komaneka at Bisma',
            'code' => 'KB',
            'is_active' => true,
        ]);

        $user = User::create([
            'name' => 'Report Officer',
            'email' => 'officer@hotel.com',
            'password' => bcrypt('password'),
            'role' => 'user',
            'hotel_id' => $hotel->id,
        ]);

        \Database\Seeders\DepartmentSeeder::class;
        $this->seed(\Database\Seeders\DepartmentSeeder::class);
        $this->seed(\Database\Seeders\ReportTemplateSeeder::class);

        $action = app(\App\Actions\CreateDailyReport::class);
        $draft = $action->execute($hotel, $user, now()->format('Y-m-d'));

        $completed = $action->execute($hotel, $user, now()->subDay()->format('Y-m-d'));
        $completed->update(['status' => 'completed', 'completed_at' => now()]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Reports This Month');
        $response->assertSee('Drafts In Progress');
        $response->assertSee('Completed Reports');
        $response->assertSee('View All Reports & History', false);
        $response->assertSee(route('reports.index'), false);
    }
}
