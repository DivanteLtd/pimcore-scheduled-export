<?php

declare(strict_types=1);

namespace Divante\ScheduledExportBundle\Model\ScheduledExportRegistry;

use Pimcore\Model;

/**
 * @property \Divante\ScheduledExportBundle\Model\ScheduledExportRegistry $model
 */
class Dao extends Model\Dao\AbstractDao
{
    const TABLE_NAME = 'bundle_scheduledexport_registry';

    /**
     * @throws \Exception
     */
    public function getById(int $id)
    {
        $data = $this->db->fetchRow(
            'SELECT * FROM ' . $this->db->quoteIdentifier(self::TABLE_NAME) . ' WHERE id = ?',
            [
                $id,
            ]
        );

        if (!$data['id']) {
            throw new \Exception(sprintf('Unable to load scheduled export registry with ID `%s`', $id));
        }

        $this->assignVariablesToModel($data);
    }

    /**
     * @throws \Exception
     */
    public function getByGridConfigId(int $gridConfigId)
    {
        $data = $this->db->fetchRow(
            'SELECT * FROM ' . self::TABLE_NAME . ' WHERE gridConfigId = ?',
            [
                $gridConfigId,
            ]
        );

        if (!$data['id']) {
            throw new \Exception(sprintf('Unable to load scheduled export registry with grid config ID `%s`', $id));
        }

        $this->assignVariablesToModel($data);
    }

    public function save()
    {
        $dataRaw = $this->model->getObjectVars();
        $data = [];

        foreach ($dataRaw as $key => $value) {
            $data[$key] = $value;
        }

        $this->db->insertOrUpdate(self::TABLE_NAME, $data);

        $lastInsertId = (int) $this->db->lastInsertId();
        if (empty($this->model->getId()) && $lastInsertId) {
            $this->model->setId($lastInsertId);
        }

        $this->model->clearDependentCache();
    }

    public function delete()
    {
        $this->db->delete(self::TABLE_NAME, ['id' => $this->model->getId()]);

        $this->model->clearDependentCache();
    }
}
