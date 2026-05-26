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
        'td' => ['colspan', 'rowspan'],
        'th' => ['colspan', 'rowspan'],
        '*' => ['class', 'title'],
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

            if (str_starts_with($name, 'on') || $name === 'style' || !in_array($name, $allowed, true)) {
                $node->removeAttributeNode($attribute);
                continue;
            }

            if (in_array($name, ['href', 'src'], true) && !self::isSafeUrl($value, $tag, $name)) {
                $node->removeAttributeNode($attribute);
                continue;
            }

            if (in_array($name, ['width', 'height', 'colspan', 'rowspan'], true) && !preg_match('/^\d{1,4}%?$/', $value)) {
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
