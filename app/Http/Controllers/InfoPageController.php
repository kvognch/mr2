<?php

namespace App\Http\Controllers;

use App\Models\InformationPage;
use App\Support\HomepageSettings;
use Illuminate\View\View;

class InfoPageController extends Controller
{
    public function __invoke(string $slug): View
    {
        $page = InformationPage::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return view('info.show', [
            'page' => $page,
            'settings' => HomepageSettings::all(),
        ]);
    }
}
