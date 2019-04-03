<?php
/**
 * @category  Wurth
 * @date      05/01/18 13:43
 * @author    Anna Zavodian <azavodian@divante.pl>
 * @copyright Copyright (c) 2017 Divante Ltd. (https://divante.co)
 */

namespace Divante\ScheduledExportBundle;

use Pimcore\Extension\Bundle\AbstractPimcoreBundle;

/**
 * Class DivanteScheduledExportBundle
 * @package Divante\ScheduledExportBundle
 */
class DivanteScheduledExportBundle extends AbstractPimcoreBundle
{
    /**
     * @return array|\Pimcore\Routing\RouteReferenceInterface[]|string[]
     */
    public function getJsPaths()
    {
        return [
            '/bundles/divantescheduledexport/js/pimcore/startup.js',
            '/bundles/divantescheduledexport/js/pimcore/process-manager/scheduled-export.js'
        ];
    }

    public function getCssPaths()
    {
        return [
            '/bundles/divantescheduledexport/css/importdefinition.css',
        ];
    }
}
