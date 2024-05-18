<?php

namespace Pagenode\Importer\Wordpress\Transform;

interface TransformInterface
{
    static public function transform(string $content): string|array;
}
