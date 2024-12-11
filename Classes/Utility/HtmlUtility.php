<?php

declare(strict_types=1);

namespace WebVision\Deepltranslate\Core\Utility;

// @todo Make class final. Overriding a static utility class does not make much sense, but better to enforce it.
// @todo Complete class and no method are used anyway. Deprecate / remove it ?
class HtmlUtility
{
    /**
     * check whether the string contains html
     *
     * @param string $string
     *
     * @todo Method is unused. Recheck and deprecate or remove it.
     */
    public static function isHtml(string $string): bool
    {
        return preg_match('/<[^<]+>/', $string, $m) != 0;
    }

    /**
     * stripoff the tags provided
     *
     * @param string[] $tags
     *
     * @todo Method is unused. Recheck and deprecate or remove it.
     */
    public static function stripSpecificTags(array $tags, string $content): string
    {
        foreach ($tags as $tag) {
            $content = preg_replace('/<\\/?' . $tag . '(.|\\s)*?>/', '', $content);
        }

        return $content;
    }
}
