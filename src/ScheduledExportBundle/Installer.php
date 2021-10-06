<?php

declare(strict_types=1);

namespace Divante\ScheduledExportBundle;

use Divante\ScheduledExportBundle\Model\ScheduledExportRegistry;
use Divante\ScheduledExportBundle\Model\ScheduledExportRegistry\Dao;
use Doctrine\DBAL\Migrations\Version;
use Doctrine\DBAL\Schema\Schema;
use Pimcore\Config;
use Pimcore\Db\ConnectionInterface;
use Pimcore\Extension\Bundle\Installer\MigrationInstaller;
use Pimcore\Migrations\MigrationManager;
use Pimcore\Model\User\Permission\Definition;
use Pimcore\Model\User\Permission\Definition\Dao as DefinitionDao;
use Pimcore\Model\WebsiteSetting\Listing;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

/**
 * Class Installer
 * @package Divante\ScheduledExportBundle
 */
class Installer extends MigrationInstaller
{
    public function migrateInstall(Schema $schema, Version $version): bool
    {
        $this->installDatabase();

        return $this->isInstalled();
    }

    public function migrateUninstall(Schema $schema, Version $version): bool
    {
        $this->uninstallDatabase();

        return !$this->isInstalled();
    }

    private function installDatabase(): void
    {
        \Pimcore\Db::get()->query(
            'CREATE TABLE IF NOT EXISTS `' . Dao::TABLE_NAME . '` (
                  `id` bigint(20) NOT NULL AUTO_INCREMENT,
                  `gridConfigId` varchar(255) NOT NULL,
                  `data` varchar(255) NOT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;'
        );

        //insert permission
        $key = Dao::TABLE_NAME;
        $permission = new Definition();
        $permission->setKey($key);

        $res = new DefinitionDao();
        $res->configure(\Pimcore\Db::get());
        $res->setModel($permission);
        $res->save();

        $list = new Listing();
        $settings = $list->getSettings();

        foreach ($settings as $item) {
            $name = $item->getName();
            if (strpos($name, 'Last_Scheduled_Export_Date')) {
                $name = explode('_', $name);

                $adaptedGridConfigId = sprintf('%s_%s', $name[0], $name[1]);

                $exportRegistry = new ScheduledExportRegistry($adaptedGridConfigId, $item->getData());
                $exportRegistry->save();
            }
        }
    }

    private function persistDatabase(): void
    {
    }

    private function uninstallDatabase(): void
    {
        \Pimcore\Db::get()->query(
            'DROP TABLE IF EXISTS `' . Dao::TABLE_NAME . '`;'
        );

        //TODO Remove permission ???
    }

    /**
     * @return bool
     */
    public function isInstalled()
    {
        $result = null;

        try {
            if (Config::getSystemConfig()) {
                $result = \Pimcore\Db::get()->fetchAll("SHOW TABLES LIKE '" . Dao::TABLE_NAME . "';");
            }
        } catch (\Exception $e) {
            return false;
        }

        return !empty($result);
    }
}
