<?php
/**
 * @date      05/01/18 13:43
 * @author    Anna Zavodian <azavodian@divante.pl>
 * @copyright Copyright (c) 2017 Divante Ltd. (https://divante.co)
 */

declare(strict_types=1);

namespace Divante\ScheduledExportBundle;

use Elements\Bundle\ProcessManagerBundle\ElementsProcessManagerBundle;
use Pimcore\Extension\Bundle\AbstractPimcoreBundle;
use Pimcore\Extension\Bundle\Traits\PackageVersionTrait;
use Pimcore\HttpKernel\Bundle\DependentBundleInterface;
use Pimcore\HttpKernel\BundleCollection\BundleCollection;

/**
 * Class ScheduledExportBundle
 * @package Divante\ScheduledExportBundle
 */
class DivanteScheduledExportBundle extends AbstractPimcoreBundle implements DependentBundleInterface
{
    use PackageVersionTrait;

    private const BUNDLE_NAME = 'DivanteScheduledExportBundle';
    public const TABLE_NAME = 'bundle_scheduledexport_registry';

    public function getInstaller()
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

    public function getNiceName()
    {
        return self::BUNDLE_NAME;
    }

    public static function registerDependentBundles(BundleCollection $collection)
    {
        $collection->addBundle(ElementsProcessManagerBundle::class, 10);
    }
}
