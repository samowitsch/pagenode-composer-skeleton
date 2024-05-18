<?php

namespace Pagenode\Importer\Wordpress;

class Post
{
    protected string $datePath = '';
    protected string $fullPath = '';
    protected string $fullPathWithHyphens = '';
    protected string $parsedContent = '';
    protected array $slider = [];
    protected array $sliderImages = [];
    public bool $hasSlider = false;
    public bool $hasContentBlocks = false;
    public bool $hasPreTags = false;
    public bool $hasCaption = false;

    public ?string $thumbnail = null;

    public function __construct(
        protected int $id = 0,
        protected string $title = '',
        protected string $urlTitle = '',
        protected string $content = '',
        protected string $date = '',
        protected array $attachments = [],
        protected array $categories = [],
        protected array $tags = [],
    ) {
        if (!empty($date)) {
            $dt = $dt = new \DateTime($date);
            $this->datePath = $dt->format('Y/m/d/');
            $this->fullPath = $this->datePath . $urlTitle;
            $this->fullPathWithHyphens = str_replace('/', '-', $this->fullPath);
        }

        $this->hasSlider = str_contains($this->content, '[slideshow_deploy',);
        $this->hasContentBlocks = str_contains($this->content, '<!-- wp:');
        $this->hasPreTags = str_contains($this->content, '<pre class');
        $this->hasCaption = str_contains($this->content, '[caption');
        $this->parsedContent = 'Todo!';
    }

    public function setTags(array $tags)
    {
        $this->tags = $tags;

        return $this;
    }


    public function getTags()
    {
        return $this->tags;
    }

    public function getTagsAsList()
    {
        return implode(', ', $this->tags);
    }

    public function setCategories(array $categories)
    {
        $this->categories = $categories;

        return $this;
    }

    public function getCategories()
    {
        return $this->categories;
    }

    public function getCategoriesAsList()
    {
        return implode(', ', $this->categories);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    public function getUrlTitle()
    {
        return $this->urlTitle;
    }

    public function setUrlTitle($urlTitle)
    {
        $this->urlTitle = $urlTitle;

        return $this;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    public function getAttachments()
    {
        return $this->attachments;
    }

    public function addAttachment($attachment)
    {
        $this->attachments[] = $attachment;

        return $this;
    }

    public function hasAttachments()
    {

        return !empty($this->attachments);
    }

    public function setAttachments($attachments)
    {
        $this->attachments = $attachments;

        return $this;
    }

    public function getDatePath()
    {
        return $this->datePath;
    }

    public function getFullPath()
    {
        return $this->fullPath;
    }

    public function getFullPathWithHyphens()
    {
        return $this->fullPathWithHyphens;
    }

    public function setSlider($slider)
    {
        $this->slider = $slider;

        return $this;
    }

    public function setSliderImages($sliderImages)
    {
        $this->sliderImages = $sliderImages;

        return $this;
    }


    public function getSliderImages()
    {
        return $this->sliderImages;
    }

    public function getThumbnail()
    {
        return $this->thumbnail;
    }

    public function setThumbnail($thumbnail)
    {
        $this->thumbnail = $thumbnail;
    }
}
