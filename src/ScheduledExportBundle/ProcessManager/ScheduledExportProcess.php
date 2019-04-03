<?php
/**
 * @date      05/01/18 13:57
 * @author    Anna Zavodian <azavodian@divante.pl>
 * @copyright Copyright (c) 2017 Divante Ltd. (https://divante.co)
 */

namespace Divante\ScheduledExportBundle\ProcessManager;

use Pimcore\Tool\Console;
use ProcessManagerBundle\Model\ExecutableInterface;
use ProcessManagerBundle\Process\ProcessInterface;

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

        $command = sprintf(
            'scheduled-export:start -g %s -f %s -a %s --filename %s -t %s -c %s --only-changes %s',
            escapeshellarg($settings['grid_config']),
            escapeshellarg($settings['objects_folder']),
            escapeshellarg($settings['asset_folder']),
            escapeshellarg($settings['asset_filename']),
            escapeshellarg($settings['add_timestamp']),
            escapeshellarg($settings['condition']),
            escapeshellarg($settings['only_changes'])
        );

        $command = PIMCORE_PROJECT_ROOT . "/bin/console " . $command;

        return Console::runPhpScriptInBackground($command);
    }
}
