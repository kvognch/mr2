<?php

namespace Tests\Unit;

use App\Support\ContractorSpreadsheet;
use PHPUnit\Framework\TestCase;

class ContractorSpreadsheetStatusTest extends TestCase
{
    public function test_explicit_status_labels_and_values_are_preserved(): void
    {
        $this->assertSame('pending', ContractorSpreadsheet::parseContractorStatus('На рассмотрении'));
        $this->assertSame('approved', ContractorSpreadsheet::parseContractorStatus('Одобрен'));
        $this->assertSame('rejected', ContractorSpreadsheet::parseContractorStatus('Отклонён'));
        $this->assertSame('pending', ContractorSpreadsheet::parseContractorStatus('pending'));
    }

    public function test_unspecified_status_defaults_to_approved(): void
    {
        $this->assertSame('approved', ContractorSpreadsheet::parseContractorStatus(null));
        $this->assertSame('approved', ContractorSpreadsheet::parseContractorStatus(''));
    }
}
