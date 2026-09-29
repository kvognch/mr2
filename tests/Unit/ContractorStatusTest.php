<?php

namespace Tests\Unit;

use App\Enums\UserRole;
use App\Models\User;
use App\Support\ContractorStatus;
use PHPUnit\Framework\TestCase;

class ContractorStatusTest extends TestCase
{
    public function test_management_default_approves_only_when_status_is_missing(): void
    {
        $manager = new User(['role' => UserRole::Manager]);
        $superadmin = new User(['role' => UserRole::Superadmin]);

        $managerPending = ContractorStatus::applyManagementDefault(['status' => 'pending'], $manager);
        $superadminRejected = ContractorStatus::applyManagementDefault(['status' => 'rejected'], $superadmin);
        $missingStatus = ContractorStatus::applyManagementDefault(['short_name' => 'Организация'], $manager);
        $blankStatus = ContractorStatus::applyManagementDefault(['status' => '  '], $superadmin);

        $this->assertTrue($manager->canManageContractors());
        $this->assertTrue($superadmin->canManageContractors());
        $this->assertSame('pending', $managerPending['status']);
        $this->assertSame('rejected', $superadminRejected['status']);
        $this->assertSame('approved', $missingStatus['status']);
        $this->assertSame('approved', $blankStatus['status']);
    }

    public function test_client_status_is_not_changed_by_manager_approval_rule(): void
    {
        $client = new User(['role' => UserRole::Client]);
        $attributes = ['status' => 'pending', 'short_name' => 'Организация'];

        $this->assertFalse($client->canManageContractors());
        $this->assertSame($attributes, ContractorStatus::applyManagementDefault($attributes, $client));
    }
}
