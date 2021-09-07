<?php
/**
 * @date      05/01/18 13:43
 * @author    Anna Zavodian <azavodian@divante.pl>
 * @copyright Copyright (c) 2017 Divante Ltd. (https://divante.co)
 */

namespace Divante\ScheduledExportBundle;

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
    protected function getComposerPackageName()
    {
        return 'divante-ltd/pimcore-scheduled-export';
    }

    /**
     * {@inheritdoc}
     */
    public function getJsPaths()
    {
        return [
            '/bundles/divantescheduledexport/js/startup.js',
            '/bundles/divantescheduledexport/js/process-manager/scheduled-export.js'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getCssPaths()
    {
        return [
            '/bundles/divantescheduledexport/css/importdefinition.css',
        ];
    }
}
