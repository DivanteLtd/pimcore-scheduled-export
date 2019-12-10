<?php
/**
 * @category    Pimcore Scheduled Export
 * @date        2019-12-09
 * @author      Bruno Ramalho <bramalho@divante.pl>
 * @copyright   Copyright (c) 2019 Divante Ltd. (https://divante.co)
 */

namespace Divante\ScheduledExportBundle\Util;

/**
 * Class TextUtil
 * @package Divante\ScheduledExportBundle\Util
 */
class TextUtil
{
    /**
     * @param string $text
     * @return string
     */
    public function cleanText(string $text)
    {
        // replace non letter or digits by -
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        // trim
        $text = trim($text, '-');

        // remove duplicate -
        $text = preg_replace('~-+~', '-', $text);

        return strtolower($text);
    }
}
