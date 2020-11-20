<?php
/**
 * @date      05/01/18 13:57
 * @author    Anna Zavodian <azavodian@divante.pl>
 * @copyright Copyright (c) 2017 Divante Ltd. (https://divante.co)
 */

namespace Divante\ScheduledExportBundle\ProcessManager;

use Pimcore\Bootstrap;
use Pimcore\Console\Application;
use Pimcore\Tool\Console;
use ProcessManagerBundle\Model\ExecutableInterface;
use ProcessManagerBundle\Process\ProcessInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Class ScheduledExportProcess
 * @package Divante\ScheduledExportBundle\ProcessManager
 * @SuppressWarnings(PHPMD)
 */
final class ScheduledExportProcess implements ProcessInterface
{
    /**
     * {@inheritdoc}
     */
    public function run(ExecutableInterface $executable, ?array $params = null)
    {
        $settings = $executable->getSettings();

        if (is_array($params)) {
            $settings = array_merge($settings, $params);
        }

        $command = sprintf(
            'scheduled-export:start -g %s -f %s -a %s --filename %s -t %s --format %s'
            . ' -c %s --only-changes %s --delimiter %s --divide_file %s --preserve_process %s --types %s'
            . ' --object-ids %s',
            escapeshellarg($settings['grid_config']),
            escapeshellarg($settings['objects_folder']),
            escapeshellarg($settings['asset_folder']),
            escapeshellarg($settings['asset_filename']),
            escapeshellarg($settings['add_timestamp']),
            escapeshellarg($settings['timestamp']),
            escapeshellarg($settings['condition']),
            escapeshellarg($settings['only_changes']),
            escapeshellarg($settings['delimiter']),
            escapeshellarg($settings['divide_file']),
            escapeshellarg($settings['preserve_process']),
            escapeshellarg($settings['types']),
            escapeshellarg($settings['object_ids'] ?? '')
        );

        $command = PIMCORE_PROJECT_ROOT . "/bin/console " . $command;

        if ($settings['foreground']) {
            return Console::runPhpScript($command);
        } else {
            return Console::runPhpScriptInBackground($command);
        };
    }
}
