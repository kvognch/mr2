<?php

namespace App\Support;

final class PublicSeo
{
    private const PAGINATED_ROUTES = ['search.index'];

    public static function canonicalUrl(): ?string
    {
        return self::isPaginationPage() ? null : request()->url();
    }

    public static function robots(): string
    {
        if (request()->query() === []) {
            return 'index, follow';
        }

        return self::isPaginationOnly() ? 'index, follow' : 'noindex, follow';
    }

    public static function title(string $title): string
    {
        $page = self::isPaginationPage() ? self::pageNumber() : null;

        return $page === null ? $title : $title.' - страница '.$page;
    }

    public static function description(string $description): string
    {
        $page = self::isPaginationPage() ? self::pageNumber() : null;

        if ($page === null) {
            return $description;
        }

        $suffix = 'Страница '.$page.'.';

        return trim($description) === '' ? $suffix : rtrim($description).' '.$suffix;
    }

    public static function ogUrl(): string
    {
        return self::canonicalUrl() ?? request()->fullUrl();
    }

    private static function isPaginationPage(): bool
    {
        return self::pageNumber() !== null
            && in_array(request()->route()?->getName(), self::PAGINATED_ROUTES, true);
    }

    private static function isPaginationOnly(): bool
    {
        $query = request()->query();

        return count($query) === 1 && array_key_exists('page', $query) && self::isPaginationPage();
    }

    private static function pageNumber(): ?int
    {
        $value = request()->query('page');

        if (! is_scalar($value)) {
            return null;
        }

        $page = filter_var($value, FILTER_VALIDATE_INT);

        return $page !== false && $page >= 1 ? (int) $page : null;
    }
}
