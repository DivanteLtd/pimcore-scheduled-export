<?php

declare(strict_types=1);

namespace Divante\ScheduledExportBundle\Model\ScheduledExportRegistry\Listing;

use Pimcore\Model;
use Divante\ScheduledExportBundle\Model\ScheduledExportRegistry;

/**
 * @property \Divante\ScheduledExportBundle\Model\ScheduledExportRegistry\Listing $model
 */
class Dao extends Model\Dao\PhpArrayTable
{
    public function load(): array
    {
        $settingsData = $this->db->fetchAll($this->model->getFilter(), $this->model->getOrder());

        $settings = [];
        foreach ($settingsData as $settingData) {
            $settings[] = ScheduledExportRegistry::getById($settingData['id']);
        }

        $this->model->setSettings($settings);

        return $settings;
    }

    /**
     * @return int
     */
    public function getTotalCount()
    {
        $data = $this->db->fetchAll($this->model->getFilter(), $this->model->getOrder());
        $amount = count($data);

        return $amount;
    }
}
