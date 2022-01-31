<?php

declare(strict_types=1);

namespace App\Service;

class LinkService
{
    public function getUrlData($url)
    {
        $result = false;

        $contents = $this->getUrlContents($url);

        if (isset($contents) && is_string($contents)) {
            $title = null;
            $metaTags = null;

            preg_match('/<title>([^>]*)<\/title>/si', $contents, $match);

            if (isset($match) && is_array($match) && count($match) > 0) {
                $title = strip_tags($match[1]);
            }

            $charset = null;
            preg_match_all('/<[\s]*meta[\s]*http-equiv="?'.'([^>"]*)"?[\s]*'.'content="?([^>"]*)"?[\s]*[\/]?[\s]*>/si', $contents, $match);
            $httpEquiv = $this->getTags($match);
            $contentType = (array_key_exists('Content-Type', $httpEquiv)) ? $httpEquiv['Content-Type']['value'] : null;
            if (null !== $contentType) {
                preg_match_all('/content="text\/html;[\s]*([^>"]*)=([^>"]*)/si', $contents, $match);
                $values = $this->getTags($match);
                $charset = (array_key_exists('charset', $values)) ? $values['charset']['value'] : null;
            }

            preg_match_all('/<[\s]*meta[\s]*name="?'.'([^>"]*)"?[\s]*'.'content="?([^>"]*)"?[\s]*[\/]?[\s]*>/si', $contents, $match);
            $metaTags = $this->getTags($match);
            $description = (array_key_exists('description', $metaTags)) ? $metaTags['description']['value'] : null;

            preg_match_all('/<[\s]*meta[\s]*property="?'.'([^>"]*)"?[\s]*'.'content="?([^>"]*)"?[\s]*[\/]?[\s]*>/si', $contents, $match);
            $properties = $this->getTags($match);
            $image = (array_key_exists('og:image', $properties)) ? $properties['og:image']['value'] : null;

            preg_match_all('/<[\s]*link[\s]*rel="?'.'([^>"]*)"?[\s]*'.'href="?([^>"]*)"?[\s]*[\/]?[\s]*>/si', $contents, $match);
            if (empty($match[0])) {
                preg_match_all('/<[\s]*link[\s]*href="?'.'([^>"]*)"?[\s]*'.'rel="?([^>"]*)"?[\s]*[\/]?[\s]*>/si', $contents, $match);
            }
            $links = $this->getTags($match);
            if (array_key_exists('https://api.w.org/', $links)) {
                $content = json_decode(file_get_contents($links['https://api.w.org/']['value'], true));
                if (null !== $content && array_key_exists('description', $content)) {
                    $description = $content->description;
                }
            }
            if (array_key_exists('image_src', $links) && null === $image) {
                $image = $links['image_src']['value'];
            }
            // if ('iso-8859-1' === $charset) {
            $title = utf8_encode($title);
            $description = utf8_encode($description);
            // }

            $result = [
                'title' => substr(html_entity_decode($title), 0, 100),
                'description' => html_entity_decode((string) $description),
                'image' => $image,
            ];
        }

        return $result;
    }

    private function getTags($match)
    {
        $metaTags = [];

        if (isset($match) && is_array($match) && 3 === count($match)) {
            $originals = $match[0];
            $names = $match[1];
            $values = $match[2];

            if (count($originals) === count($names) && count($names) === count($values)) {
                for ($i = 0, $limiti = count($names); $i < $limiti; ++$i) {
                    $metaTags[strtolower($names[$i])] = [
                        'html' => htmlentities($originals[$i]),
                        'value' => $values[$i],
                    ];
                }
            }
        }

        return $metaTags;
    }

    private function getUrlContents($url, $maximumRedirections = null, $currentRedirection = 0)
    {
        $result = false;

        $contents = @file_get_contents($url);

        // Check if we need to go somewhere else

        if (isset($contents) && is_string($contents)) {
            preg_match_all('/<[\s]*meta[\s]*http-equiv="?REFRESH"?'.'[\s]*content="?[0-9]*;[\s]*URL[\s]*=[\s]*([^>"]*)"?'.'[\s]*[\/]?[\s]*>/si', $contents, $match);

            if (isset($match) && is_array($match) && 2 === count($match) && 1 === count($match[1])) {
                if (! isset($maximumRedirections) || $currentRedirection < $maximumRedirections) {
                    return $this->getUrlContents($match[1][0], $maximumRedirections, ++$currentRedirection);
                }

                $result = false;
            } else {
                $result = $contents;
            }
        }

        return $contents;
    }

    private function getUrlContentsCurl($url)
    {
        $user_agent = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/72.0.3626.109 Safari/537.36';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

        $html = curl_exec($ch);
        curl_close($ch);
    }
}
