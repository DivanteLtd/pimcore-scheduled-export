<?php
/**
 * @date      05/01/18 13:43
 * @author    Anna Zavodian <azavodian@divante.pl>
 * @copyright Copyright (c) 2017 Divante Ltd. (https://divante.co)
 */

declare(strict_types=1);

namespace Divante\ScheduledExportBundle;

use Divante\ScheduledExportBundle\Migrations\Installer;
use Pimcore\Extension\Bundle\AbstractPimcoreBundle;
use Pimcore\Extension\Bundle\Traits\PackageVersionTrait;

/**
 * Class ScheduledExportBundle
 * @package Divante\ScheduledExportBundle
 */
class DivanteScheduledExportBundle extends AbstractPimcoreBundle
{
    use PackageVersionTrait;

    public function getInstaller(): Installer
    {
        return $this->container->get(Installer::class);
    }

    /**
     * {@inheritdoc}
     */
    protected function getComposerPackageName(): string
    {
        return 'divante-ltd/pimcore-scheduled-export';
    }

    /**
     * {@inheritdoc}
     */
    public function getJsPaths(): array
    {
        return [
            '/bundles/divantescheduledexport/js/startup.js',
            '/bundles/divantescheduledexport/js/process-manager/scheduled-export.js'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getCssPaths(): array
    {
        return [
            '/bundles/divantescheduledexport/css/importdefinition.css',
        ];
    }
}
