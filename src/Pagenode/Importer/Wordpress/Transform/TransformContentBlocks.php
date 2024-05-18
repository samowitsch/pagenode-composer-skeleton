<?php

namespace Pagenode\Importer\Wordpress\Transform;

final class TransformContentBlocks implements TransformInterface
{
    /**
     * possible wp blockquotes:
     * <!-- wp:paragraph --> ... <!-- /wp:paragraph -->
     * <!-- wp:quote --> ... <!-- /wp:quote -->
     * <!-- wp:code --> ... <!-- /wp:code --> inside with <pre...><code>....</code></pre>
     * <!-- wp:preformatted --> ... <!-- /wp:preformatted --> inside with <pre class="wp-block-preformatted"> ... </pre>
     * <!-- wp:image {"id":2129,"sizeSlug":"large","linkDestination":"media"} --> ... <!-- /wp:image -->
     */
    static public string $pattern = "/(<!-- (wp:|\/wp:).*-->)/";

    static public function transform(string $content): string
    {
        return preg_replace(TransformContentBlocks::$pattern, '', $content);
    }
}
