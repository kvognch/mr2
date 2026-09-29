<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Http\Middleware\SetFilamentMemoryLimit;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

class ContractorAdminListTest extends TestCase
{
    use DatabaseTransactions;

    public function test_contractor_category_list_renders_with_the_admin_memory_limit(): void
    {
        $previousLimit = ini_get('memory_limit');
        ini_set('memory_limit', '128M');

        try {
            $admin = User::factory()->create([
                'role' => UserRole::Superadmin,
                'is_active' => true,
            ]);

            $this->actingAs($admin)
                ->get('/dashboard/contractors/contractors')
                ->assertOk();

            $this->assertSame('256M', ini_get('memory_limit'));
            $this->assertContains(SetFilamentMemoryLimit::class, Livewire::getPersistentMiddleware());
        } finally {
            if (is_string($previousLimit)) {
                ini_set('memory_limit', $previousLimit);
            }
        }
    }
}
