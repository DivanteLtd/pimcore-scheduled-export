<?php

declare(strict_types=1);

namespace Divante\ScheduledExportBundle\Model\ScheduledExportRegistry;

use Divante\ScheduledExportBundle\Model\ScheduledExportRegistry;
use Pimcore\Model;

/**
 * @method \Divante\ScheduledExportBundle\Model\ScheduledExportRegistry\Listing\Dao getDao()
 * @method ScheduledExportRegistry[] load()
 * @method int getTotalCount()
 */
class Listing extends Model\Listing\JsonListing
{
    /**
     * @var array|null
     */
    protected $exports;

    public function setExports(array $exports): void
    {
        $this->exports = $exports;
    }

    public function getExports(): array
    {
        if ($this->exports === null) {
            $this->getDao()->load();
        }

        return $this->exports;
    }
}
