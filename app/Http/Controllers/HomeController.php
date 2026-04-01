<?php

namespace App\Http\Controllers;

use App\Models\ServiceReview;
use App\Support\HomepageSettings;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('home.index', [
            'settings' => HomepageSettings::all(),
            'serviceReviews' => ServiceReview::query()
                ->approved()
                ->latest()
                ->get()
                ->map(fn (ServiceReview $review): array => [
                    'id' => $review->id,
                    'title' => $review->title,
                    'text' => $review->body,
                    'author' => $review->author_name,
                    'authRole' => $review->author_role,
                    'stars' => $review->rating,
                ])
                ->values()
                ->all(),
        ]);
    }
}
