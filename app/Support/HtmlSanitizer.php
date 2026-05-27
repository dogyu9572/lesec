<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

class HtmlSanitizer
{
    private const ALLOWED_TAGS = [
        'a', 'b', 'blockquote', 'br', 'code', 'div', 'em', 'font', 'h1', 'h2', 'h3',
        'h4', 'h5', 'h6', 'hr', 'i', 'iframe', 'img', 'li', 'ol', 'p', 'pre', 's',
        'source', 'span', 'strike', 'strong', 'table', 'tbody', 'td', 'th', 'thead',
        'tr', 'u', 'ul', 'video',
    ];

    private const DROP_WITH_CONTENT = ['script', 'style', 'object', 'embed', 'form', 'input', 'button', 'textarea', 'select'];

    private const ALLOWED_ATTRIBUTES = [
        'a' => ['href', 'target', 'title', 'rel'],
        'img' => ['src', 'alt', 'title', 'width', 'height'],
        'iframe' => ['src', 'title', 'width', 'height', 'allowfullscreen'],
        'video' => ['src', 'width', 'height', 'controls'],
        'source' => ['src', 'type'],
        'font' => ['color', 'face', 'size'],
        'table' => ['align', 'border', 'cellpadding', 'cellspacing', 'width', 'height'],
        'tr' => ['align', 'valign'],
        'td' => ['align', 'valign', 'colspan', 'rowspan', 'width', 'height'],
        'th' => ['align', 'valign', 'colspan', 'rowspan', 'width', 'height'],
        '*' => ['align', 'class', 'style', 'title'],
    ];

    private const ALLOWED_STYLE_PROPERTIES = [
        'background-color', 'border', 'border-bottom', 'border-collapse', 'border-color', 'border-left',
        'border-right', 'border-style', 'border-top', 'border-width', 'color', 'font-size', 'font-style',
        'font-weight', 'height', 'line-height', 'margin', 'margin-bottom', 'margin-left', 'margin-right',
        'margin-top', 'max-width', 'padding', 'padding-bottom', 'padding-left', 'padding-right',
        'padding-top', 'text-align', 'text-decoration', 'vertical-align', 'width',
    ];

    public static function clean(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML(
            '<?xml encoding="UTF-8"><div id="__html_sanitizer_root__">' . $html . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $document->getElementById('__html_sanitizer_root__');
        if (!$root) {
            return '';
        }

        self::sanitizeChildren($root);

        $clean = '';
        foreach ($root->childNodes as $child) {
            $clean .= $document->saveHTML($child);
        }

        return trim($clean);
    }

    private static function sanitizeChildren(DOMNode $node): void
    {
        for ($child = $node->firstChild; $child !== null;) {
            $next = $child->nextSibling;
            self::sanitizeNode($child);
            $child = $next;
        }
    }

    private static function sanitizeNode(DOMNode $node): void
    {
        if (!$node instanceof DOMElement) {
            return;
        }

        $tag = strtolower($node->tagName);

        if (in_array($tag, self::DROP_WITH_CONTENT, true)) {
            $node->parentNode?->removeChild($node);
            return;
        }

        if (!in_array($tag, self::ALLOWED_TAGS, true)) {
            self::unwrap($node);
            return;
        }

        self::sanitizeAttributes($node, $tag);
        self::sanitizeChildren($node);
    }

    private static function sanitizeAttributes(DOMElement $node, string $tag): void
    {
        for ($i = $node->attributes->length - 1; $i >= 0; $i--) {
            $attribute = $node->attributes->item($i);
            if (!$attribute) {
                continue;
            }

            $name = strtolower($attribute->name);
            $value = trim(html_entity_decode($attribute->value, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            $allowed = array_merge(
                self::ALLOWED_ATTRIBUTES['*'] ?? [],
                self::ALLOWED_ATTRIBUTES[$tag] ?? []
            );

            if (str_starts_with($name, 'on') || !in_array($name, $allowed, true)) {
                $node->removeAttributeNode($attribute);
                continue;
            }

            if ($name === 'style') {
                $style = self::sanitizeStyle($value);
                if ($style === '') {
                    $node->removeAttributeNode($attribute);
                    continue;
                }

                $node->setAttribute($name, $style);
                continue;
            }

            if (in_array($name, ['href', 'src'], true) && !self::isSafeUrl($value, $tag, $name)) {
                $node->removeAttributeNode($attribute);
                continue;
            }

            if (in_array($name, ['width', 'height'], true) && !self::isSafeLength($value)) {
                $node->removeAttributeNode($attribute);
                continue;
            }

            if (in_array($name, ['colspan', 'rowspan', 'border', 'cellpadding', 'cellspacing'], true) && !preg_match('/^\d{1,4}$/', $value)) {
                $node->removeAttributeNode($attribute);
                continue;
            }

            if ($name === 'align' && !in_array(strtolower($value), ['left', 'center', 'right', 'justify'], true)) {
                $node->removeAttributeNode($attribute);
                continue;
            }

            if ($name === 'valign' && !in_array(strtolower($value), ['top', 'middle', 'bottom', 'baseline'], true)) {
                $node->removeAttributeNode($attribute);
                continue;
            }

            if ($name === 'color' && !self::isSafeColor($value)) {
                $node->removeAttributeNode($attribute);
                continue;
            }

            if ($name === 'size' && !preg_match('/^[1-7]$/', $value)) {
                $node->removeAttributeNode($attribute);
                continue;
            }

            if ($name === 'face' && !preg_match('/^[\p{L}\p{N}\s,"\-_.]+$/u', $value)) {
                $node->removeAttributeNode($attribute);
                continue;
            }

            if ($name === 'target' && !in_array($value, ['_self', '_blank'], true)) {
                $node->removeAttributeNode($attribute);
            }
        }

        if ($tag === 'a' && $node->getAttribute('target') === '_blank') {
            $node->setAttribute('rel', 'noopener noreferrer');
        }
    }

    private static function isSafeUrl(string $url, string $tag, string $attribute): bool
    {
        if ($url === '' || str_starts_with($url, '#') || str_starts_with($url, '/') || str_starts_with($url, './') || str_starts_with($url, '../')) {
            return true;
        }

        if ($tag === 'img' && $attribute === 'src' && preg_match('/^data:image\/(?:png|jpe?g|gif|webp);base64,/i', $url)) {
            return true;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        if ($attribute === 'href') {
            return in_array($scheme, ['http', 'https', 'mailto', 'tel'], true);
        }

        return in_array($scheme, ['http', 'https'], true);
    }

    private static function sanitizeStyle(string $style): string
    {
        $clean = [];

        foreach (explode(';', $style) as $declaration) {
            if (!str_contains($declaration, ':')) {
                continue;
            }

            [$property, $value] = array_map('trim', explode(':', $declaration, 2));
            $property = strtolower($property);
            $value = preg_replace('/\s+/', ' ', $value) ?? '';

            if (
                $property === ''
                || $value === ''
                || str_contains($value, '!important')
                || !in_array($property, self::ALLOWED_STYLE_PROPERTIES, true)
                || !self::isSafeStyleValue($property, $value)
            ) {
                continue;
            }

            $clean[] = $property . ': ' . $value;
        }

        return implode('; ', $clean);
    }

    private static function isSafeStyleValue(string $property, string $value): bool
    {
        $lowerValue = strtolower($value);

        if (preg_match('/(?:expression|url\s*\(|javascript:|vbscript:|data:|@import|behavior\s*:|-moz-binding)/i', $lowerValue)) {
            return false;
        }

        if (in_array($property, ['color', 'background-color', 'border-color'], true)) {
            return self::isSafeColorList($value);
        }

        if ($property === 'text-align') {
            return in_array($lowerValue, ['left', 'center', 'right', 'justify'], true);
        }

        if ($property === 'vertical-align') {
            return in_array($lowerValue, ['top', 'middle', 'bottom', 'baseline', 'text-top', 'text-bottom'], true)
                || self::isSafeLength($value);
        }

        if ($property === 'font-weight') {
            return in_array($lowerValue, ['normal', 'bold', 'bolder', 'lighter'], true)
                || preg_match('/^[1-9]00$/', $value);
        }

        if ($property === 'font-style') {
            return in_array($lowerValue, ['normal', 'italic', 'oblique'], true);
        }

        if ($property === 'text-decoration') {
            return preg_match('/^(?:none|underline|line-through|overline)(?:\s+(?:underline|line-through|overline))*$/i', $value);
        }

        if ($property === 'border-collapse') {
            return in_array($lowerValue, ['collapse', 'separate'], true);
        }

        if ($property === 'border-style') {
            return preg_match('/^(?:none|solid|dashed|dotted|double)(?:\s+(?:none|solid|dashed|dotted|double)){0,3}$/i', $value);
        }

        if (str_ends_with($property, 'width') || str_ends_with($property, 'height') || str_starts_with($property, 'margin') || str_starts_with($property, 'padding')) {
            return self::isSafeLengthList($value);
        }

        if ($property === 'font-size' || $property === 'line-height') {
            return self::isSafeLength($value) || in_array($lowerValue, ['normal', 'small', 'medium', 'large', 'x-small', 'x-large'], true);
        }

        if (str_starts_with($property, 'border')) {
            return self::isSafeBorderValue($value);
        }

        return false;
    }

    private static function isSafeColorList(string $value): bool
    {
        if (self::isSafeColor($value)) {
            return true;
        }

        foreach (preg_split('/\s+/', trim($value)) ?: [] as $color) {
            if ($color !== '' && !self::isSafeColor($color)) {
                return false;
            }
        }

        return trim($value) !== '';
    }

    private static function isSafeColor(string $value): bool
    {
        $value = trim($value);

        return preg_match('/^#[0-9a-f]{3}(?:[0-9a-f]{3})?$/i', $value)
            || preg_match('/^rgba?\(\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}(?:\s*,\s*(?:0|1|0?\.\d+))?\s*\)$/i', $value)
            || preg_match('/^[a-z]+$/i', $value);
    }

    private static function isSafeLengthList(string $value): bool
    {
        foreach (preg_split('/\s+/', trim($value)) ?: [] as $length) {
            if ($length !== '' && !self::isSafeLength($length)) {
                return false;
            }
        }

        return trim($value) !== '';
    }

    private static function isSafeLength(string $value): bool
    {
        return preg_match('/^(?:auto|0|-?\d{1,4}(?:\.\d{1,2})?(?:px|em|rem|%|pt)?)$/i', trim($value));
    }

    private static function isSafeBorderValue(string $value): bool
    {
        foreach (preg_split('/\s+/', trim($value)) ?: [] as $part) {
            if ($part === '') {
                continue;
            }

            if (
                self::isSafeLength($part)
                || self::isSafeColor($part)
                || preg_match('/^(?:none|solid|dashed|dotted|double)$/i', $part)
            ) {
                continue;
            }

            return false;
        }

        return trim($value) !== '';
    }

    private static function unwrap(DOMElement $node): void
    {
        $parent = $node->parentNode;
        if (!$parent) {
            return;
        }

        while ($node->firstChild) {
            $parent->insertBefore($node->firstChild, $node);
        }

        $parent->removeChild($node);
    }
}
