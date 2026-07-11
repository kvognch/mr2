<?php

namespace App\Http\Controllers;

use App\Models\ContractorTariff;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class ContractorTariffController extends Controller
{
    public function destroy(Request $request, ContractorTariff $contractorTariff): RedirectResponse|Response
    {
        $contractorTariff->loadMissing('contractor');

        $this->authorize('update', $contractorTariff->contractor);

        Storage::disk('public')->delete($contractorTariff->path);
        $contractorTariff->delete();

        if ($request->expectsJson()) {
            return response()->noContent();
        }

        return back();
    }
}
