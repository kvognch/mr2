<?php

namespace App\Support;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

final class EditorialHtml
{
    public static function clean(string $html): string
    {
        $config = (new HtmlSanitizerConfig)->allowSafeElements()->allowRelativeLinks()->allowRelativeMedias()->allowElement('details', ['open'])->allowElement('summary')->allowElement('aside')->allowAttribute('class', '*')->allowAttribute('id', '*')->withMaxInputLength(500000);

        return (new HtmlSanitizer($config))->sanitize($html);
    }
}
