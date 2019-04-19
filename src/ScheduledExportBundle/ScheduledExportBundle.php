<?php
/**
 * @date      05/01/18 13:43
 * @author    Anna Zavodian <azavodian@divante.pl>
 * @copyright Copyright (c) 2017 Divante Ltd. (https://divante.co)
 */

namespace Divante\ScheduledExportBundle;

use Pimcore\Extension\Bundle\AbstractPimcoreBundle;

/**
 * Class ScheduledExportBundle
 * @package Divante\ScheduledExportBundle
 */
class ScheduledExportBundle extends AbstractPimcoreBundle
{
    /**
     * @return string
     */
    public function getComposerPackageName(): string
    {
        return 'divante/scheduled-export';
    }

    /**
     * @return string
     */
    public function getNiceName()
    {
        return 'Scheduled Export';
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return 'Scheduled Export lets you run ordinary grid exports in background.';
    }

    /**
     * @return array|\Pimcore\Routing\RouteReferenceInterface[]|string[]
     */
    public function getJsPaths()
    {
        return [
            '/bundles/scheduledexport/pimcore/js/startup.js',
            '/bundles/scheduledexport/pimcore/js/process-manager/scheduled-export.js'
        ];
    }

    /**
     * @return array|\Pimcore\Routing\RouteReferenceInterface[]|string[]
     */
    public function getCssPaths()
    {
        return [
            '/bundles/scheduledexport/pimcore/css/importdefinition.css',
        ];
    }
}
