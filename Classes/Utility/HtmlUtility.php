<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Utility;

class HtmlUtility
{
    /**
     * check whether the string contains html
     *
     * @param string $string
     */
    public static function isHtml(string $string): bool
    {
        return preg_match('/<[^<]+>/', $string, $m) != 0;
    }

    /**
     * stripoff the tags provided
     *
     * @param string[] $tags
     */
    public static function stripSpecificTags(array $tags, string $content): string
    {
        foreach ($tags as $tag) {
            $content = preg_replace('/<\\/?' . $tag . '(.|\\s)*?>/', '', $content);
        }

        return $content;
    }
}
