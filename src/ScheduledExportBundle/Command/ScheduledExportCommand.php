<?php
/**
 * @date      05/01/18 14:03
 * @author    Anna Zavodian <azavodian@divante.pl>
 * @copyright Copyright (c) 2017 Divante Ltd. (https://divante.co)
 */

declare(strict_types=1);

namespace Divante\ScheduledExportBundle\Command;

use Divante\ScheduledExportBundle\Export\Export;
use Carbon\Carbon;
use Elements\Bundle\ProcessManagerBundle\ExecutionTrait;
use Elements\Bundle\ProcessManagerBundle\MetaDataFile;
use Pimcore\Console\AbstractCommand;
use Pimcore\Model\DataObject\AbstractObject;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ScheduledExportCommand
 *
 * @package Divante\ScheduledExportBundle\Command
 */
class ScheduledExportCommand extends AbstractCommand
{
    use ExecutionTrait;

    protected static $defaultName = 'scheduled-export:start';

    private $container;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct();

        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription('Run Scheduled Export.')
            ->setHelp(<<<EOT
The <info>%command.name%</info> runs export of object based on predefined grid config.
EOT
            )
            ->addOption(
                'gridconfig',
                'g',
                InputOption::VALUE_REQUIRED,
                'Gridconfig ID'
            )
            ->addOption(
                'folder',
                'f',
                InputOption::VALUE_REQUIRED,
                'Source folder'
            )
            ->addOption(
                'asset',
                'a',
                InputOption::VALUE_REQUIRED,
                'Target folder'
            )
            ->addOption(
                'filename',
                'p',
                InputOption::VALUE_OPTIONAL,
                'Export filename'
            )
            ->addOption(
                'condition',
                'c',
                InputOption::VALUE_OPTIONAL,
                'Objects filter condition'
            )
            ->addOption(
                'timestamp',
                't',
                InputOption::VALUE_OPTIONAL,
                'Append timestamp'
            )
            ->addOption(
                'format',
                '',
                InputOption::VALUE_OPTIONAL,
                'Timestamp format'
            )
            ->addOption(
                'only-changes',
                '',
                InputOption::VALUE_OPTIONAL,
                'Export only changes from last export'
            )
            ->addOption(
                'delimiter',
                '',
                InputOption::VALUE_OPTIONAL,
                'Set your own delimiter'
            )
            ->addOption(
                'divide_file',
                '',
                InputOption::VALUE_OPTIONAL,
                'Divide file into parts with n lines'
            )
            ->addOption(
                'types',
                '',
                InputOption::VALUE_OPTIONAL,
                'Set what types should be exported; e. g. "object,variant"; defaults to default list settings'
            )
            ->addOption(
                'object-ids',
                '',
                InputOption::VALUE_OPTIONAL,
                'Export only specified object ids'
            )
            ->addOption(
                'add_utf_bom',
                '',
                InputOption::VALUE_OPTIONAL,
                'Add BOM (Byte Order Mark)'
            )
            ->addOption(
                'monitoring-item-id',
                null,
                InputOption::VALUE_REQUIRED,
                'Contains the monitoring item if executed via the Pimcore backend'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        self::initProcessManager($input->getOption('monitoring-item-id'), ['autoCreate' => true]);

        $monitoringItem = self::getMonitoringItem();

        $metDataFileObject = MetaDataFile::getById('sample-id');

        $start = Carbon::now();
        if ($ts = $metDataFileObject->getData()['lastRun']) {
            $lastRun = Carbon::createFromTimestamp($ts);
        } else {
            $lastRun = Carbon::now();
        }

        AbstractObject::setHideUnpublished(false);

        $export = new Export(
            $monitoringItem,
            $this->container,
            $input
        );

        $export->execute();

        $monitoringItem->getLogger()->debug('Last Run: ' . $lastRun->format(Carbon::DEFAULT_TO_STRING_FORMAT));

        $metDataFileObject->setData(['lastRun' => $start->getTimestamp()])->save();

        $monitoringItem->setMessage('Job finished')->setCompleted();
    }
}
