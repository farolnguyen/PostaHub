<?php

namespace App\Support;

use Mews\Purifier\Facades\Purifier;

class HtmlSanitizer
{
    /**
     * Sanitize rich-text HTML from CKEditor to mitigate XSS.
     */
    public static function clean(string $html): string
    {
        return Purifier::clean($html, [
            'HTML.Doctype' => 'HTML 4.01 Transitional',
            'HTML.Allowed' => 'p,br,b,strong,i,em,u,s,blockquote,ul,ol,li,h2,h3,h4,span[style|class],a[href|title|target|rel],img[src|alt|title|width|height|class|style],code,pre',
            'CSS.AllowedProperties' => 'text-align,font-weight,font-style,text-decoration,max-width,color,background-color',
            'Attr.AllowedFrameTargets' => ['_blank'],
            'AutoFormat.AutoParagraph' => false,
            'AutoFormat.RemoveEmpty' => true,
            'URI.DisableExternalResources' => false,
            'URI.DisableResources' => false,
        ]);
    }
}

