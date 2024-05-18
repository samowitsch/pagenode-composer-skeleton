<?php

namespace Pagenode\Importer\Wordpress\Transform;

final class TransformResourceUri implements TransformInterface
{
    static public string $pattern = "/((http|https):\/\/(wordpress|www).motions-media.de\/wp-content)/";

    static public function transform(string $content): string
    {
        return preg_replace(TransformResourceUri::$pattern, '', $content);
    }

    static public function getUriResources(
        $content,
        $hrefPattern = '/<a(?:.*?)href="(?P<uri>.*?)"(?:.*?)>/',
        $srcPattern = '/<img(?:.*)src="(?P<uri>.*?)"(?:.*?)>/',
        $filter = 'http'
    ) {
        preg_match_all($hrefPattern, $content, $hrefMatches);
        preg_match_all($srcPattern, $content, $srcMatches);

        return array_filter(
            array_merge($hrefMatches['uri'], $srcMatches['uri']),
            function ($item) use ($filter) {
                return !str_starts_with($item, $filter);
            }
        );
    }

    static public function transformUrisToRelative(array $uris = [])
    {
        foreach ($uris as $key => $uri) {
            $uris[$key] = explode('wp-content', $uri)[1];
        }

        return $uris;
    }
}
