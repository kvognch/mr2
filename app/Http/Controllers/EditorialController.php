<?php

namespace App\Http\Controllers;

use App\Models\BlogArticle;
use App\Models\EditorialRubric;
use App\Models\GeoUnit;
use App\Models\NewsPublication;
use App\Models\ResourceType;
use App\Models\Setting;
use App\Support\HomepageSettings;
use Illuminate\Http\Request;

class EditorialController extends Controller
{
    public function index(Request $request)
    {
        $kind = $request->routeIs('blog.*') ? 'blog' : 'news';
        $model = $kind === 'blog' ? BlogArticle::class : NewsPublication::class;
        $data = $request->validate(['direction' => 'nullable|string|max:255', 'rubric' => 'nullable|string|max:255', 'region' => 'nullable|integer|min:1', 'page' => 'nullable|integer|min:1']);
        $query = $model::published()->with(['rubric', 'directions', 'regions:id,name'])->orderByDesc('published_at')->orderByDesc('id');
        if (! empty($data['direction'])) {
            $query->whereHas('directions', fn ($q) => $q->where('slug', $data['direction']));
        }
        if (! empty($data['rubric'])) {
            $query->whereHas('rubric', fn ($q) => $q->where('slug', $data['rubric']));
        }
        if (! empty($data['region'])) {
            $region = (int) $data['region'];
            $query->where(function ($q) use ($kind, $region) {
                $q->whereHas('regions', fn ($r) => $r->where('geo_units.id', $region));
                if ($kind === 'blog') {
                    $q->orWhereDoesntHave('regions');
                } else {
                    $q->orWhere('is_federal', true);
                }
            });
        }
        $slideQuery = clone $query;
        $items = $query->paginate(12)->withQueryString();
        if ($request->header('X-Editorial-More') === '1') {
            return view('editorial.cards', compact('items', 'kind'));
        }
        $home = $kind === 'blog' ? (json_decode((string) Setting::where('key', 'blog.home')->value('value'), true) ?? []) : ['interval' => 7];
        if ($kind === 'blog') {
            $ids = array_column($home['slides'] ?? [], 'article_id');
            $found = BlogArticle::published()->with(['rubric', 'directions', 'regions:id,name'])->whereIn('id', $ids)->get()->keyBy('id');
            $slides = collect($ids)->unique()->map(fn ($id) => $found->get($id))->filter()->values();
        } else {
            $slides = $slideQuery->limit(3)->get();
        }
        $latestNews = NewsPublication::published()->with(['directions', 'regions:id,name'])->when($data['region'] ?? null, fn ($q, $id) => $q->where(fn ($q) => $q->where('is_federal', true)->orWhereHas('regions', fn ($r) => $r->where('geo_units.id', $id))))->orderByDesc('published_at')->orderByDesc('id')->limit(3)->get();

        return view('editorial.index', compact('kind', 'items', 'slides', 'home', 'latestNews') + ['directions' => ResourceType::orderBy('name')->get(), 'rubrics' => EditorialRubric::where('kind', $kind)->where('is_visible', true)->orderBy('sort_order')->orderBy('id')->get(), 'regions' => GeoUnit::where('admin_level', 4)->orderBy('name')->get(['id', 'name']), 'settings' => HomepageSettings::all()]);
    }

    public function show(Request $request, string $slug)
    {
        $kind = $request->routeIs('blog.*') ? 'blog' : 'news';
        $model = $kind === 'blog' ? BlogArticle::class : NewsPublication::class;
        $item = $model::published()->with(['rubric', 'directions', 'regions:id,name'])->where('slug', $slug)->firstOrFail();
        $related = $item->relatedArticles()->published()->with('rubric')->limit(3)->get();

        return view('editorial.show', compact('item', 'kind', 'related') + ['settings' => HomepageSettings::all()]);
    }
}
