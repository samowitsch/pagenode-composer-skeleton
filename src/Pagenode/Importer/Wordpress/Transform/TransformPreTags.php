<?php

namespace Pagenode\Importer\Wordpress\Transform;

final class TransformPreTags implements TransformInterface
{
    // https://stackoverflow.com/questions/7167279/regex-select-all-text-between-tags#comment137375642_52093397
    static public string $pattern = '/(?:<pre[^>]*>)/';

    static public function transform(string $content): string
    {
        return preg_replace(TransformPreTags::$pattern, '<pre>', $content);
    }

}