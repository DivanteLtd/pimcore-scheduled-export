<?php
/**
 * @category    Pimcore Scheduled Export
 * @date        2019-12-09
 * @author      Bruno Ramalho <bramalho@divante.pl>
 * @copyright   Copyright (c) 2019 Divante Ltd. (https://divante.co)
 */

namespace Tests\Divante\ScheduledExportBundle\Util;

use Divante\ScheduledExportBundle\Util\TextUtil;
use PHPUnit\Framework\TestCase;

/**
 * Class TextUtilTest
 * @package Tests\Divante\ScheduledExportBundle\Util
 */
class TextUtilTest extends TestCase
{
    /** @var TextUtil $textUtil */
    private $textUtil;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->textUtil = new TextUtil();
    }

    /**
     * @return void
     */
    public function testCanCleanText()
    {
        $text = "a\" b* c- d: e( f- g? h\ i'";
        $actual = $this->textUtil->cleanText($text);
        $expected = "a-b-c-d-e-f-g-h-i";

        $this->assertEquals($expected, $actual);

        $text = "/some/directory/maybe with spaces/";
        $actual = $this->textUtil->cleanText($text);
        $expected = "/some/directory/maybe with spaces";

        $this->assertEquals($actual, $expected);
    }
}
