<?php

namespace Pagenode\Importer\Wordpress\Transform;

final class TransformSlider implements TransformInterface
{
    static public string $pattern = "/\[slideshow_deploy id='(?P<id>.*)'\]+/";
    static public string $replacePattern = "/\[slideshow_deploy id='.*'\]+/";

    static public function transform(string $content): string
    {
        return preg_match(TransformSlider::$pattern, $content, $matches);
    }

    static public function transformWithData(string $content, array $sliderImages = []): string
    {
        $slider = '';
        if (!empty($sliderImages)){
            $slider = '<div class="swiper"><div class="swiper-wrapper">' . PHP_EOL;
            foreach($sliderImages as $sliderImage){
                $slider.= sprintf('<div class="swiper-slide"><img src="%s" loading="lazy" /></div>' . PHP_EOL, $sliderImage);
            }   
            $slider .= '</div><div class="swiper-pagination"></div><div class="swiper-button-prev"></div><div class="swiper-button-next"></div><div class="swiper-scrollbar"></div></div>' . PHP_EOL;        
        } 

        return preg_replace(TransformSlider::$replacePattern, (string) $slider, $content);
    }


    static public function getId(string $content){
        preg_match(TransformSlider::$pattern, $content, $matches);

        if(isset($matches['id']) && is_numeric($matches['id'])){
            return $matches['id'];
        }

        return null;

    }

    private function querySlider($id){

    }
}
