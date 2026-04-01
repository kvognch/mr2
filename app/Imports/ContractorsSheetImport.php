<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class ContractorsSheetImport implements ToCollection
{
    public function collection(Collection $collection): void
    {
        // Parsed via Excel::toCollection(); no per-row side effects are needed here.
    }
}
