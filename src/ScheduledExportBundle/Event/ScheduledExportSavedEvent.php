<?php

namespace Divante\ScheduledExportBundle\Event;

use Pimcore\Model\DataObject\Concrete;

/**
 * Class ScheduledExportSavedEvent
 * @package Divante\ScheduledExportBundle\Event
 */
class ScheduledExportSavedEvent extends \Symfony\Component\EventDispatcher\Event
{
    const NAME = 'divante.scheduled_export.scheduled_export_saved';

    /** @var string */
    private $filenames;

    /**
     * ScheduledExportSavedEvent constructor.
     * @param string[] $filenames
     */
    public function __construct(string $filename)
    {
        $this->filename = $filename;
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }
}
