<?php

namespace Divante\ScheduledExportBundle\Event;

use Pimcore\Model\DataObject\Concrete;

/**
 * Class BatchExportedEvent
 * @package Divante\ScheduledExportBundle\Event
 */
class BatchExportedEvent extends \Symfony\Component\EventDispatcher\Event
{
    const NAME = 'divante.scheduled_export.batch_exported';

    /** @var string[] */
    private $objectsIds;

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
