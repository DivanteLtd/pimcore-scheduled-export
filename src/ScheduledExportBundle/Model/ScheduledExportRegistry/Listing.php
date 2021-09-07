<?php

declare(strict_types=1);

namespace Divante\ScheduledExportBundle\Model\ScheduledExportRegistry;

use Pimcore\Model;

/**
 * @method \Divante\ScheduledExportBundle\Model\ScheduledExportRegistry\Listing\Dao getDao()
 * @method \Divante\ScheduledExportBundle\Model\ScheduledExportRegistry[] load()
 * @method int getTotalCount()
 */
class Listing extends Model\Listing\JsonListing
{
    /**
     * @var array|null
     */
    protected $exports = null;

    /**
     * @param array $settings
     */
    public function setSettings(array $exports)
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
