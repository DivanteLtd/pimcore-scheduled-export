<?php

declare(strict_types=1);

namespace Divante\ScheduledExportBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class BatchExportedEvent extends Event
{
    public const NAME = 'divante.scheduled_export.batch_exported';

    /** @var string[] */
    private array $objectsIds;

    /**
     * BatchExportedEvent constructor.
     * @param string[] $objectsIds
     */
    public function __construct(array $objectsIds)
    {
        $this->objectsIds = $objectsIds;
    }

    /**
     * @return string[]
     */
    public function getObjectsIds(): array
    {
        return $this->objectsIds;
    }
}
