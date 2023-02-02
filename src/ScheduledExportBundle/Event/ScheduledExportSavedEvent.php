<?php

declare(strict_types=1);

namespace Divante\ScheduledExportBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class ScheduledExportSavedEvent
 *
 * @package Divante\ScheduledExportBundle\Event
 */
class ScheduledExportSavedEvent extends Event
{
    public const NAME = 'divante.scheduled_export.scheduled_export_saved';

    private string $filename;

    public function __construct(string $filename)
    {
        $this->filename = $filename;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }
}
