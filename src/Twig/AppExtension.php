<?php

namespace App\Twig;

use App\Controller\RegistrationController;
use Twig\TwigFilter;
use Twig\Extension\AbstractExtension;



class AppExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('imgPath', [$this, 'imgPath']),
        ];
    }

    public function imgPath($content, $media)
    {
        if (RegistrationController::OUT_PDF === $media) {
            $pattern = '#(.*) src="\/images(.+)#';
            $replace = '$1 src="./images$2';
            $count = preg_match_all($pattern, $content);
            if ($count) {
                for ($i = 0; $i < $count; $i++) {
                    $content = preg_replace($pattern, $replace, $content);
                }
            }
        }

        return $content;
    }
}