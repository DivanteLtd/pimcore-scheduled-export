<?php

declare(strict_types=1);

namespace Divante\ScheduledExportBundle\Model\ScheduledExportRegistry;

use Divante\ScheduledExportBundle\DivanteScheduledExportBundle;
use Exception;
use Pimcore\Db\Helper;
use Pimcore\Model;

/**
 * @property \Divante\ScheduledExportBundle\Model\ScheduledExportRegistry $model
 */
class Dao extends Model\Dao\AbstractDao
{
    /**
     * @throws Exception
     */
    public function getById(int $id): void
    {
        $data = $this->db->fetchAssociative(
            'SELECT * FROM ' . $this->db->quoteIdentifier(DivanteScheduledExportBundle::TABLE_NAME) . ' WHERE id = ?',
            [
                $id,
            ]
        );

        if (!$data['id']) {
            throw new Exception(sprintf('Unable to load scheduled export registry with ID `%s`', $id));
        }

        $this->assignVariablesToModel($data);
    }

    /**
     * @throws Exception
     */
    public function getByGridConfigId(string $gridConfigId): void
    {
        $data = $this->db->fetchAssociative(
            'SELECT * FROM ' . DivanteScheduledExportBundle::TABLE_NAME . ' WHERE gridConfigId = ?',
            [
                $gridConfigId,
            ]
        );

        if (!$data['id']) {
            throw new Exception(sprintf(
                'Unable to load scheduled export registry with grid config ID `%s`',
                $gridConfigId
            ));
        }

        $this->assignVariablesToModel($data);
    }

    public function save(): void
    {
        $dataRaw = $this->model->getObjectVars();
        $data = [];

        foreach ($dataRaw as $key => $value) {
            $data[$key] = $value;
        }

        Helper::insertOrUpdate($this->db, DivanteScheduledExportBundle::TABLE_NAME, $data);

        $lastInsertId = (int) $this->db->lastInsertId();
        if (empty($this->model->getId()) && $lastInsertId) {
            $this->model->setId($lastInsertId);
        }

        $this->model->clearDependentCache();
    }

    public function delete(): void
    {
        $this->db->delete(DivanteScheduledExportBundle::TABLE_NAME, ['id' => $this->model->getId()]);

        $this->model->clearDependentCache();
    }
}
