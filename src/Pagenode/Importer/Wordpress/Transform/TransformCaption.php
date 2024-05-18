<?php

namespace Pagenode\Importer\Wordpress\Transform;

final class TransformCaption implements TransformInterface
{
    /**
     * transform caption shorthand [caption id="attachment_1871" align="alignnone" width="474"] ... [/caption]
     * and extract linked image. ignoring caption text.
     */
    static public string $pattern = "/\[caption.*\[\/caption\]/";
    static public string $captionContentPattern = "/\[caption.*\](?P<img><a.*<\/a>?)(?P<caption>.*?)\[\/caption\]/";

    static public function transform(string $content): string
    {
        preg_match(TransformCaption::$captionContentPattern, $content, $matches);
        return preg_replace(TransformCaption::$pattern, $matches['img'], $content);
    }
}
