<?php
/**
 * @date      05/01/18 14:03
 * @author    Anna Zavodian <azavodian@divante.pl>
 * @copyright Copyright (c) 2017 Divante Ltd. (https://divante.co)
 */

namespace Divante\ScheduledExportBundle\Command;

use Divante\ScheduledExportBundle\Export\Export;
use Pimcore\Console\AbstractCommand;
use Pimcore\Model\DataObject\AbstractObject;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Class ScheduledExportCommand
 *
 * @package Divante\ScheduledExportBundle\Command
 */
class ScheduledExportCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('scheduled-export:start')
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
                'types',
                '',
                InputOption::VALUE_OPTIONAL,
                'Set what types should be exported; e. g. "object,variant"; defaults to default list settings'
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        AbstractObject::setHideUnpublished(false);

        $container = $this->getContainer();

        $export = new Export(
            $input->getOption("gridconfig"),
            $input->getOption("folder"),
            $input->getOption("asset"),
            $container,
            (string) $input->getOption("condition"),
            (string) $input->getOption("filename"),
            (string) $input->getOption("timestamp"),
            (string) $input->getOption("only-changes"),
            (string) $input->getOption("format"),
            (string) $input->getOption("delimiter"),
            (string) $input->getOption("types")
        );

        $export->export();
    }
}
