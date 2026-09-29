<?php

namespace Tests\Unit;

use App\Support\ContractorTerritoryScope;
use PHPUnit\Framework\TestCase;

class ContractorTerritoryScopeTest extends TestCase
{
    public function test_assigned_territory_includes_its_descendants_and_ancestors_but_not_sibling_descendants(): void
    {
        $parentIds = [
            1 => null, // Leningrad Oblast
            2 => 1, // Vsevolozhsky District
            3 => 2, // Zanevskoye settlement
            4 => 3, // A descendant of Zanevskoye
            5 => 1, // Kirovsky District
            6 => 5, // Mgin settlement
            7 => 6, // A descendant of Mgin
        ];
        $descendantsByTerritory = [
            1 => [2, 3, 4, 5, 6, 7],
            2 => [3, 4],
            3 => [4],
            4 => [],
            5 => [6, 7],
            6 => [7],
            7 => [],
        ];

        $scopeIds = ContractorTerritoryScope::forAssignments([3], $parentIds, $descendantsByTerritory);

        $this->assertEqualsCanonicalizing([1, 2, 3, 4], $scopeIds);
        $this->assertNotContains(5, $scopeIds);
        $this->assertNotContains(6, $scopeIds);
        $this->assertNotContains(7, $scopeIds);
    }
}
