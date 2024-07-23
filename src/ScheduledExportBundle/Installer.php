<?php

declare(strict_types=1);

namespace Divante\ScheduledExportBundle;

use Divante\ScheduledExportBundle\Model\ScheduledExportRegistry;
use Pimcore\Db\Connection;
use Pimcore\Db\ConnectionInterface;
use Pimcore\Extension\Bundle\Installer\SettingsStoreAwareInstaller;
use Pimcore\Model\User\Permission\Definition;
use Pimcore\Model\WebsiteSetting\Listing;

class Installer extends SettingsStoreAwareInstaller
{
    protected array $permissions = [
        Enums\Permissions::VIEW,
        Enums\Permissions::CONFIGURE,
        Enums\Permissions::EXECUTE,
    ];

    public function install(): void
    {
        $this->createPermissions();
        $this->createTables();
        parent::install();
    }

    protected function createPermissions(): void
    {
        foreach ($this->permissions as $permissionKey) {
            Definition::create($permissionKey);
        }
    }

    public function needsReloadAfterInstall(): bool
    {
        return true;
    }

    /**
     * @return Connection|ConnectionInterface
     */
    protected function getDb(): ConnectionInterface|Connection
    {
        return \Pimcore\Db::get();
    }

    protected function createTables(): void
    {
        $db = $this->getDb();
        $db->query(
            'CREATE TABLE IF NOT EXISTS `' . DivanteScheduledExportBundle::TABLE_NAME . '` (
              `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
              `gridConfigId` VARCHAR(255) NOT NULL,
              `data` VARCHAR(255),
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;'
        );

        $list = new Listing();
        $settings = $list->getSettings();

        foreach ($settings as $item) {
            $name = $item->getName();
            if (strpos($name, 'Last_Scheduled_Export_Date')) {
                $name = explode('_', $name);

                $adaptedGridConfigId = sprintf('%s_%s', $name[0], $name[1]);

                $exportRegistry = new ScheduledExportRegistry($adaptedGridConfigId, (string) $item->getData());
                $exportRegistry->save();
            }
        }
    }

    public function uninstall(): void
    {
        $tables = [
            DivanteScheduledExportBundle::TABLE_NAME,
        ];
        foreach ($tables as $table) {
            $this->getDb()->query('DROP TABLE IF EXISTS ' . $table);
        }

        foreach ($this->permissions as $permissionKey) {
            $this->getDb()->query(
                'DELETE FROM users_permission_definitions WHERE ' . $this->getDb()->quoteIdentifier('key').' = :permission',
                ['permission' => $permissionKey]
            );
        }

        parent::uninstall();
    }
}
