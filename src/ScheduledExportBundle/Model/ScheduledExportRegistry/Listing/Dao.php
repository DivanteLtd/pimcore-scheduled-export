<?php

declare(strict_types=1);

namespace Divante\ScheduledExportBundle\Model\ScheduledExportRegistry\Listing;

use Divante\ScheduledExportBundle\Model\ScheduledExportRegistry\Listing;
use Pimcore\Model;
use Divante\ScheduledExportBundle\Model\ScheduledExportRegistry;

/**
 * @property Listing $model
 */
class Dao extends Model\Dao\PhpArrayTable
{
    public function load(): array
    {
        $exportsData = $this->db->fetchAll($this->model->getFilter(), $this->model->getOrder());

        $exports = [];
        foreach ($exportsData as $exportData) {
            $exports[] = ScheduledExportRegistry::getById($exportData['id']);
        }

        $this->model->setExports($exports);

        return $exports;
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
