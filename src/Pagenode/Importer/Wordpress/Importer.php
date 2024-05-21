<?php

namespace Pagenode\Importer\Wordpress;

use Pagenode\Importer\Wordpress\Transform\TransformCaption;
use Pagenode\Importer\Wordpress\Transform\TransformContentBlocks;
use Pagenode\Importer\Wordpress\Transform\TransformPreTags;
use Pagenode\Importer\Wordpress\Transform\TransformResourceUri;
use Pagenode\Importer\Wordpress\Transform\TransformSlider;

class Importer
{
    public const CONFIG_FILE = '../config/import-wordpress-config.yaml';

    protected $db = null;

    protected $posts = [];

    protected $filesToCopy = [];

    public function __construct(
        private array $settings = []
    ) {
        if (empty($this->settings['paths']['importFolder'])) {
            throw new \Exception('Missing importFolder!', 1713650853);
        }
        if (empty($this->settings['paths']['exportFolder'])) {
            throw new \Exception('Missing exportFolder!', 1713652066);
        }

        $this->db = new Query($settings);
    }

    public function fetch()
    {
        $this->posts = $this->db->queryPosts();
        return $this;
    }


    public function transformContent()
    {
        foreach ($this->posts as $key => $post) {

            $post->setContent(
                TransformResourceUri::transform(
                    $post->getContent()
                )
            );

            $this->filesToCopy = array_merge(
                $this->filesToCopy,
                TransformResourceUri::getUriResources($post->getContent())
            );

            if ($thumbnail = $post->getThumbnail()) {
                $this->filesToCopy[] = $thumbnail;
            }

            if ($post->hasSlider) {
                $sliderImages = TransformResourceUri::transformUrisToRelative(
                    $post->getSliderImages()
                );

                $post->setContent(
                    TransformSlider::transformWithData(
                        $post->getContent(),
                        $sliderImages
                    )
                );

                $this->filesToCopy = array_merge(
                    $this->filesToCopy,
                    $sliderImages
                );
            }

            if ($post->hasContentBlocks) {
                $post->setContent(
                    TransformContentBlocks::transform(
                        $post->getContent()
                    )
                );
            }

            if ($post->hasPreTags) {
                $post->setContent(
                    TransformPreTags::transform(
                        $post->getContent()
                    )
                );
            }

            if ($post->hasCaption) {
                $post->setContent(
                    TransformCaption::transform(
                        $post->getContent()
                    )
                );
            }

            $this->posts[$key] = $post;
        }
        return $this;
    }

    public function copyAssets()
    {
        foreach ($this->filesToCopy as $file) {
            $src = $_SERVER["DOCUMENT_ROOT"] . '/..' . $this->settings['paths']['importFolder'] . '/wp-content';
            $dest = $_SERVER["DOCUMENT_ROOT"];

            if (!file_exists(dirname($dest . $file))) {
                mkdir(dirname($dest . $file), 0777, true);
            }
            copy($src . $file, $dest . $file);
        }

        return $this;
    }

    public function generatePagenodeMarkdownFiles()
    {
        foreach ($this->getPosts() as $key => $post) {
            # flat usage
            $fileName = $_SERVER["DOCUMENT_ROOT"] . $this->settings['paths']['exportFolder'] . '/' . $post->getFullPathWithHyphens() . '.md';

            $attachments = implode(', ', $post->getAttachments());
            $content = $post->getContent();

            $content = <<<CONTENT
title: {$post->getTitle()}
date: {$post->getDate()}
fullPath: {$post->getFullPath()}
fullPathWithHyphens: {$post->getFullPathWithHyphens()}
wpUrlTitle: {$post->getUrlTitle()}
wpAttachments: {$attachments}
thumbnail: {$post->getThumbnail()}
postId: {$post->getId()}
categories: {$post->getCategoriesAsList()}
tags: {$post->getTagsAsList()}

---

# {$post->getTitle()}
{$content}
CONTENT;

            if (!file_exists($fileName)) {
                file_put_contents($fileName, $content);
            } else {
                unlink($fileName);
                file_put_contents($fileName, $content);
            }
        }
    }


    public function getPosts()
    {
        return $this->posts;
    }

    public function getPostsWithSlider()
    {
        $res = [];
        foreach ($this->posts as $post) {
            if ($post->hasSlider) {
                $res[] = $post;
            }
        }
        return $res;
    }

    public function getPostsWithContentBlocks()
    {
        $res = [];
        foreach ($this->posts as $post) {
            if ($post->hasContentBlocks) {
                $res[] = $post;
            }
        }
        return $res;
    }
    public function getPostsWithPreTags()
    {
        $res = [];
        foreach ($this->posts as $post) {
            if ($post->hasPreTags) {
                $res[] = $post;
            }
        }
        return $res;
    }
}
