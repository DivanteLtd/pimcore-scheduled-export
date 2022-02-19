<?php

declare(strict_types=1);

namespace Divante\ScheduledExportBundle\Migrations;

use Divante\ScheduledExportBundle\Model\ScheduledExportRegistry;
use Divante\ScheduledExportBundle\Model\ScheduledExportRegistry\Dao;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Migrations\Version;
use Doctrine\DBAL\Schema\Schema;
use Pimcore\Config;
use Pimcore\Db;
use Pimcore\Extension\Bundle\Installer\MigrationInstaller;
use Pimcore\Model\WebsiteSetting\Listing;

/**
 * Class Installer
 * @package Divante\ScheduledExportBundle
 */
class Installer extends MigrationInstaller
{
    /**
     * @throws DBALException
     */
    public function migrateInstall(Schema $schema, Version $version): bool
    {
        $this->installDatabase();

        return $this->isInstalled();
    }

    /**
     * @throws DBALException
     */
    public function migrateUninstall(Schema $schema, Version $version): bool
    {
        $this->uninstallDatabase();

        return !$this->isInstalled();
    }

    /**
     * @throws DBALException
     */
    private function installDatabase(): void
    {
        Db::get()->query(
            'CREATE TABLE IF NOT EXISTS `' . Dao::TABLE_NAME . '` (
                  `id` bigint(20) NOT NULL AUTO_INCREMENT,
                  `gridConfigId` varchar(255) NOT NULL,
                  `data` varchar(255),
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

    private function persistDatabase(): void
    {
    }

    /**
     * @throws DBALException
     */
    private function uninstallDatabase(): void
    {
        Db::get()->query(
            'DROP TABLE IF EXISTS `' . Dao::TABLE_NAME . '`;'
        );

        //TODO Remove permission ???
    }

    public function isInstalled(): bool
    {
        $result = null;

        try {
            if (Config::getSystemConfig()) {
                $result = Db::get()->fetchAll("SHOW TABLES LIKE '" . Dao::TABLE_NAME . "';");
            }
        } catch (\Exception $e) {
            return false;
        }

        return !empty($result);
    }
}
