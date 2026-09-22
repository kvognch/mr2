<?php

namespace App\Http\Controllers;

use App\Models\Contractor;
use App\Models\InformationPage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    private const ORGANIZATION_CHUNK_SIZE = 10000;

    public function index(): Response
    {
        $sitemaps = [route('sitemap.static')];

        foreach ($this->organizationSitemapParts() as $part) {
            $sitemaps[] = route('sitemap.organizations', ['part' => sprintf('%03d', $part)]);
        }

        $items = collect($sitemaps)
            ->map(fn (string $url): string => '<sitemap><loc>'.$this->escape($url).'</loc></sitemap>')
            ->implode('');

        return $this->xmlResponse(
            '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.$items.'</sitemapindex>'
        );
    }

    public function staticMap(): Response
    {
        $urls = [
            ['loc' => route('home')],
            ['loc' => route('search.index')],
        ];

        InformationPage::query()
            ->where('is_active', true)
            ->whereNotNull('body')
            ->where('body', '<>', '')
            ->orderBy('id')
            ->get(['slug', 'updated_at'])
            ->each(function (InformationPage $page) use (&$urls): void {
                $urls[] = [
                    'loc' => route('info.show', ['slug' => $page->slug]),
                    'lastmod' => $page->updated_at?->toAtomString(),
                ];
            });

        return $this->xmlResponse($this->renderUrlSet($urls));
    }

    public function organizations(int $part): Response
    {
        if ($part < 1) {
            abort(404);
        }

        $fromId = (($part - 1) * self::ORGANIZATION_CHUNK_SIZE) + 1;
        $toId = $part * self::ORGANIZATION_CHUNK_SIZE;
        $organizations = $this->publishedOrganizationsQuery()
            ->whereBetween('id', [$fromId, $toId])
            ->orderBy('id')
            ->get(['slug', 'updated_at']);

        if ($organizations->isEmpty()) {
            abort(404);
        }

        $urls = $organizations
            ->map(fn (Contractor $contractor): array => [
                'loc' => route('organizations.show', ['slug' => $contractor->slug]),
                'lastmod' => $contractor->updated_at?->toAtomString(),
            ])
            ->all();

        return $this->xmlResponse($this->renderUrlSet($urls));
    }

    /**
     * @return array<int>
     */
    private function organizationSitemapParts(): array
    {
        $maxId = (int) $this->publishedOrganizationsQuery()->max('id');

        if ($maxId === 0) {
            return [];
        }

        $lastPart = (int) ceil($maxId / self::ORGANIZATION_CHUNK_SIZE);
        $parts = [];

        for ($part = 1; $part <= $lastPart; $part++) {
            $fromId = (($part - 1) * self::ORGANIZATION_CHUNK_SIZE) + 1;
            $toId = $part * self::ORGANIZATION_CHUNK_SIZE;

            if ($this->publishedOrganizationsQuery()->whereBetween('id', [$fromId, $toId])->exists()) {
                $parts[] = $part;
            }
        }

        return $parts;
    }

    private function publishedOrganizationsQuery(): Builder
    {
        return Contractor::query()
            ->where('status', 'approved')
            ->whereNotNull('slug')
            ->where('slug', '<>', '')
            ->whereNotNull('short_name')
            ->where('short_name', '<>', '');
    }

    /**
     * @param  array<int, array{loc: string, lastmod?: ?string}>  $urls
     */
    private function renderUrlSet(array $urls): string
    {
        $items = collect($urls)
            ->map(function (array $url): string {
                $lastmod = isset($url['lastmod']) && $url['lastmod'] !== ''
                    ? '<lastmod>'.$this->escape($url['lastmod']).'</lastmod>'
                    : '';

                return '<url><loc>'.$this->escape($url['loc']).'</loc>'.$lastmod.'</url>';
            })
            ->implode('');

        return '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.$items.'</urlset>';
    }

    private function xmlResponse(string $body): Response
    {
        return response('<?xml version="1.0" encoding="UTF-8"?>'.$body)
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }
}
