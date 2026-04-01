<?php

namespace App\Http\Controllers;

use App\Exports\ContractorsExport;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ContractorExportController extends Controller
{
    public function __invoke(): BinaryFileResponse
    {
        abort_unless(auth()->user()?->isSuperadmin() || auth()->user()?->isManager(), 403);

        return Excel::download(
            new ContractorsExport(),
            'contractors-' . now()->format('Ymd_His') . '.xlsx'
        );
    }
}
