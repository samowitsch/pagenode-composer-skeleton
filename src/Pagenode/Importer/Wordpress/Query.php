<?php

namespace Pagenode\Importer\Wordpress;

use Doctrine\DBAL\DriverManager;


class Query
{

    private $connection = null;


    public function __construct(
        private array $settings = []

    ) {
        if (empty($this->settings['db']['connectionParams'])) {
            throw new \Exception('Missing needed db $connectionParams!', 1713650093);
        }
        $this->connection = DriverManager::getConnection($this->settings['db']['connectionParams']);
    }


    public function queryPosts()
    {
        $posts = [];
        $sql = 'SELECT * FROM wp_posts WHERE post_status = "publish" and post_type = "post"';
        $stmt = $this->connection->prepare($sql);

        $oldPosts = $stmt->executeQuery()->fetchAllAssociative();

        foreach ($oldPosts as $oldPost) {
            $post = new Post(
                id: $oldPost['ID'],
                title: $oldPost['post_title'],
                urlTitle: $oldPost['post_name'],
                content: $oldPost['post_content'],
                date: $oldPost['post_date']
            );

            $post->setCategories(
                $this->queryCategories($oldPost['ID'])
            );

            $post->setTags(
                $this->queryTags($oldPost['ID'])
            );

            $post->setAttachments(
                $this->queryAttachments($oldPost['ID'])
            );

            if ($thumbnail = $this->queryThumbnail($oldPost['ID'])) {
                $thumbnailPath = $this->checkThumbnail($thumbnail);
                $post->setThumbnail($thumbnailPath);
            }

            if ($post->hasSlider) {
                preg_match_all("/\[slideshow_deploy id='(.*)'\]+/", $post->getContent(), $matches);
                $post->setSlider([
                    'pattern' => $matches[0][0],
                    'id' => $matches[1][0]
                ]);

                $post->setSliderImages(
                    $this->querySlider($matches[1][0])
                );
            }
            $posts[] = $post;
        }
        return $posts;
    }

    public function queryAttachments($id)
    {
        $sql = 'SELECT * FROM wp_posts WHERE post_type = "attachment" AND post_parent = ' . $id;
        $stmt = $this->connection->prepare($sql);
        $results =  $stmt->executeQuery()->fetchAllAssociative();
        $attachments = [];
        foreach ($results as $result) {
            $attachments[] = $result['guid'];
        }
        return $attachments;
    }

    /**
     * search slider for old slideshow-jquery-image-gallery plugin
     * @see https://plugins.trac.wordpress.org/browser/slideshow-jquery-image-gallery
     */
    public function querySlider(int $id)
    {
        $sql = 'SELECT * FROM wp_postmeta WHERE meta_key = "slides" AND post_id = ' . $id;
        $stmt = $this->connection->prepare($sql);
        $results =  $stmt->executeQuery()->fetchAllAssociative();

        $meta_value = unserialize($results[0]['meta_value']);

        $sliderImages = [];
        foreach ($meta_value as $sliderImage) {
            $result = $this->querySliderAttachments($sliderImage['postId']);
            if (!empty($result)) {
                $sliderImages[] = $result[0];
            }
        }
        return $sliderImages;
    }

    public function querySliderAttachments($id)
    {
        $sql = 'SELECT * FROM wp_posts WHERE post_type = "attachment" AND ID = ' . $id;
        $stmt = $this->connection->prepare($sql);
        $results =  $stmt->executeQuery()->fetchAllAssociative();
        $attachments = [];
        foreach ($results as $result) {
            $attachments[] = $result['guid'];
        }
        return $attachments;
    }

    public function queryCategories($id)
    {
        return $this->queryTermsByType($id, 'category');
    }

    public function queryTags($id)
    {
        return $this->queryTermsByType($id, 'post_tag');
    }

    public function queryTermsByType($id, $type){
        $sql = <<<SQL
SELECT name FROM wp_term_relationships 
INNER JOIN wp_term_taxonomy 
    ON (wp_term_taxonomy.term_taxonomy_id=wp_term_relationships.term_taxonomy_id 
    AND wp_term_taxonomy.taxonomy='{$type}')
INNER JOIN wp_terms 
    ON (wp_terms.term_id=wp_term_taxonomy.term_id )
WHERE object_id={$id}
SQL;

        $stmt = $this->connection->prepare($sql);
        $results =  $stmt->executeQuery()->fetchAllAssociative();
        if (empty($results)) {
            return [];
        }

        $out = [];
        foreach($results as $result){
            $out[] = ucwords($result['name']);
        }
        return $out;
    }


    public function queryThumbnail($id)
    {
        $sql = 'SELECT * FROM wp_postmeta WHERE meta_key = "_thumbnail_id" and post_id = ' . $id;
        $stmt = $this->connection->prepare($sql);
        $result =  $stmt->executeQuery()->fetchAllAssociative();

        if (empty($result)) {
            return null;
        }

        $id = $result[0]['meta_value'];

        $sql = 'SELECT * FROM wp_postmeta where meta_key = "_wp_attachment_metadata" and post_id = ' . $id;
        $stmt = $this->connection->prepare($sql);
        $result =  $stmt->executeQuery()->fetchAllAssociative();

        $postmeta = unserialize($result[0]['meta_value']);

        if (isset($postmeta['sizes']['post-thumbnail']['file'])) {
            return $postmeta['sizes']['post-thumbnail']['file'];
        }

        $sql = 'SELECT * FROM wp_posts where ID = ' . $id;
        $stmt = $this->connection->prepare($sql);
        $result =  $stmt->executeQuery()->fetchAllAssociative();

        if (!empty($result)) {
            return $result[0]['guid'];
        }


        return null;
    }

    private function checkThumbnail(string $thumbnail)
    {
        $check = parse_url($thumbnail);

        if (isset($check['scheme']) && isset($check['host'])) {
            // thumbnail was stored as uri
            $path = str_replace('/wp-content', '', $check['path']);
            return $path;
        } else {
            // thumbnail was stored as filename only
            // search for thumbnail in wordpress project folder
            $cmd = 'cd /var/www/html' . $this->settings['paths']['importFolder'] . ' && find ./ -type f -iname "*' . $check['path'] . '"';

            $result = shell_exec($cmd);
            $sanitizedRelativePath = str_replace('./wp-content', '', trim($result));
            return $sanitizedRelativePath;
        }
    }
}
