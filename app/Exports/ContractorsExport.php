<?php

namespace App\Exports;

use App\Models\Contractor;
use App\Support\ContractorSpreadsheet;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ContractorsExport implements FromArray, WithEvents, WithHeadings, WithStyles
{
    public function headings(): array
    {
        return ContractorSpreadsheet::headings();
    }

    public function array(): array
    {
        return Contractor::query()
            ->with(['categories', 'territories', 'smrResourceTypes', 'pirResourceTypes', 'rating'])
            ->orderBy('id')
            ->get()
            ->map(fn (Contractor $contractor): array => array_values(ContractorSpreadsheet::contractorToRow($contractor)))
            ->all();
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $event->sheet->getDelegate()->setSelectedCell('A1');
            },
        ];
    }
}
