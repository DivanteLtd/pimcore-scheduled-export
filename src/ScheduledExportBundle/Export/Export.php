<?php
/**
 * @date      08/01/18 11:29
 * @author    Anna Zavodian <azavodian@divante.pl>
 * @copyright Copyright (c) 2017 Divante Ltd. (https://divante.co)
 */

namespace Divante\ScheduledExportBundle\Export;

use Pimcore\Bundle\AdminBundle\Controller\Admin\DataObject\DataObjectHelperController;
use Pimcore\Localization\LocaleService;
use Pimcore\Logger;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject\ClassDefinition;
use Pimcore\Model\DataObject\Folder;
use Pimcore\Model\DataObject\Listing;
use Pimcore\Model\GridConfig;
use Pimcore\Model\WebsiteSetting;
use Pimcore\Tool;
use ProcessManagerBundle\Model\Process;
use ProcessManagerBundle\ProcessManagerBundle;
use ProcessManagerBundle\Repository\ProcessRepository;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;

/**
 * Class Export
 * @package Divante\ScheduledExportBundle\Service
 * @SuppressWarnings(PHPMD)
 */
class Export
{
    const SETTINGS = '{"enableInheritance":true,"delimiter":"%delimiter%"}';

    const WS_NAME = 'Last_Scheduled_Export_Date';

    const INTERNAL_BATCH_SIZE = 1000;

    private $gridConfig;
    private $objectsFolder;
    private $assetFolder;
    private $condition;
    private $fileName;
    private $timestamp;
    private $timestampFormat;
    private $importStartTimestamp;
    private $container;
    private $delimiter;

    /** @var Input $input */
    private $input;
    /** @var Output $output */
    private $output;
    /** @var Process $process */
    private $process;
    /** @var Listing $listing */
    private $listing;
    /** @var bool $onlyChanges */
    private $onlyChanges;
    /** @var int $changesFromTimestamp */
    private $changesFromTimestamp;
    /** @var ProcessRepository $processRepository */
    private $processRepository;

    /**
     * Export constructor.
     * @param string $gridConfig
     * @param string $objectsFolder
     * @param string $assetFolder
     * @param ContainerInterface $container
     * @param string|null $condition
     * @param string|null $fileName
     * @param string $timestamp
     * @param string $onlyChanges
     * @throws \Exception
     */
    public function __construct(
        $container,
        $input,
        $output
    ) {
        $this->setContainer($container);
        $this->setTimestamp((string) $input->getOption("timestamp"));
        $this->setGridConfig($input->getOption("gridconfig"));
        $this->setObjectsFolder($input->getOption("folder"));
        $this->setOnlyChanges((string) $input->getOption("only-changes"));
        $this->setAssetFolder($input->getOption("asset"));
        $this->setCondition((string) $input->getOption("condition"));
        $this->setTimestampFormat((string) $input->getOption("format"));
        $this->setFilename(\Pimcore\File::getValidFilename((string) $input->getOption("filename")));
        $this->setDelimiter((string) $input->getOption("delimiter"));
        $this->input = $input;
        $this->output = $output;
        $this->process = new Process(
            sprintf("Scheduled Export (%s) - %s", $this->objectsFolder, $this->gridConfig->getName()),
            "Scheduled Export",
            "Starting"
        );
        $this->process->setStarted(time());
        $this->process->setStoppable(true);
        $this->processRepository = $this->container->get('process_manager.repository.process');
    }

    public function getExportSetting() : WebsiteSetting
    {
        $settings = WebsiteSetting::getByName($this->gridConfig->getId() .
            "_" . Folder::getByPath($this->objectsFolder)->getId() .
            "_" . self::WS_NAME);
        if (!$settings) {
            $settings = new WebsiteSetting();
            $settings->setName($this->gridConfig->getId() .
                "_" . Folder::getByPath($this->objectsFolder)->getId() .
                "_" . self::WS_NAME);
            $settings->save();
        }

        return $settings;
    }

    /**
     * @param bool $timestamp
     * @return void
     */
    public function setTimestamp(bool $timestamp): void
    {
        if ($timestamp == "1") {
            $this->timestamp = true;
        } else {
            $this->timestamp = false;
        }
    }

    /**
     * @param string $gridId
     * @return void
     * @throws \Exception
     */
    public function setGridConfig(string $gridId): void
    {
        $this->gridConfig = GridConfig::getById($gridId);
    }

    /**
     * @param string $onlyChanges
     * @return void
     */
    public function setOnlyChanges(string $onlyChanges): void
    {
        $settings = $this->getExportSetting();
        if ($onlyChanges === "1") {
            $this->onlyChanges = true;
            $this->changesFromTimestamp = strtotime($settings->getData());
        } else {
            $this->onlyChanges = false;
            $this->changesFromTimestamp = 0;
        }

        $this->importStartTimestamp = time();
    }

    /**
     * @param string $objectsFolder
     * @return void
     */
    public function setObjectsFolder($objectsFolder): void
    {
        $this->objectsFolder = $objectsFolder;
    }

    /**
     * @param string $assetFolder
     * @return void
     */
    public function setAssetFolder($assetFolder): void
    {
        $this->assetFolder = $assetFolder;
    }

    /**
     * @param string|null $delimiter
     * @return void
     */
    public function setDelimiter($delimiter): void
    {
        if (!$delimiter) {
            $delimiter = ";";
        }
        $this->delimiter = $delimiter;
    }

    /**
     * @param string|null $condition
     * @return void
     */
    public function setCondition($condition): void
    {
        $this->condition = $condition;
    }

    /**
     * @param string $fileName
     * @return void
     */
    public function setFileName(string $fileName): void
    {
        if ($this->timestamp) {
            if ($this->timestampFormat == "") {
                $format = "-%s";
            } else {
                $format = $this->timestampFormat;
            }

            $fileName = $fileName . strftime($format);
        }

        $this->fileName = $fileName;
    }

    /**
     * @param ContainerInterface $container
     * @return void
     */
    public function setContainer($container): void
    {
        $this->container = $container;
    }

    /**
     * @return string
     */
    public function getTimestampFormat(): string
    {
        return $this->timestampFormat;
    }

    /**
     * @param mixed $timestampFormat
     */
    public function setTimestampFormat(string $timestampFormat): void
    {
        $this->timestampFormat = $timestampFormat;
    }

    /**
     * @return void
     */
    public function export(): void
    {
        $this->process->setStatus(ProcessManagerBundle::STATUS_RUNNING);
        $this->process->save();
        $this->prepareListing();
        $objectIds = $this->getObjectIds();
        $localeService = new LocaleService();
        $controller = new DataObjectHelperController();
        $controller->setContainer($this->container);
        $filenames = [];

        $this->process->setMessage("Starting");
        $this->process->save();
        foreach ($objectIds as $objectIdBatch) {
            $filename = uniqid();
            $filenames[] = $filename;
            $request = $this->prepareRequest($objectIdBatch, $filename);
            if (!count($request->request->get('ids'))) {
                return;
            }

            $controller->doExportAction($request, $localeService);
            $this->process = $this->processRepository->find($this->process->getId());
            if ($this->process->getStatus() == ProcessManagerBundle::STATUS_STOPPING) {
                foreach ($filenames as $filename) {
                    $tmpFile = PIMCORE_SYSTEM_TEMP_DIRECTORY . "/" . $filename . ".csv";
                    unlink($tmpFile);
                    if (!$this->input->getOption('preserve_process')) {
                        $this->process->delete();
                    } else {
                        $this->process->setStatus(ProcessManagerBundle::STATUS_STOPPED);
                        $this->process->setMessage("Stopped by user");
                        $this->process->save();
                        return;
                    }
                }
            }
            $this->process->progress(count($objectIdBatch));
            $this->process->setMessage(sprintf("Running (%d/%d)", $this->process->getProgress(), $this->process->getTotal()));
            $this->process->save();
            \Pimcore::collectGarbage();

        }
        $this->process->setMessage("Saving results");
        $this->process->save();
        $this->saveFileInAssets($filenames);
        $this->process->setStatus(ProcessManagerBundle::STATUS_COMPLETED);
        $this->process->setMessage(sprintf("Done (%d objects exported)", $this->process->getProgress()));
        $this->process->save();
        $this->updateSettingsDate();
        if (!$this->input->getOption('preserve_process')) {
            $this->process->delete();
        }
    }

    /**
     * @return Request
     */
    protected function prepareRequest(array $objectIds, string $filename): Request
    {
        $request = Request::createFromGlobals();

        $request->request->set('fileHandle', $filename);
        $request->request->set('ids', $objectIds);
        $request->request->set('settings', str_replace("%delimiter%", $this->delimiter, self::SETTINGS));
        $request->request->set('classId', $this->gridConfig->classId);
        $request->request->set('initial', '1');
        $request->request->set('fields', $this->prepareFields());
        $request->request->set('language', 'en_GB');

        return $request;
    }

    /**
     *
     */
    protected function prepareListing(): void
    {
        $objectsFolder = Folder::getByPath($this->objectsFolder);
        $className = "\\Pimcore\\Model\\DataObject\\"
            . ucfirst(ClassDefinition::getById($this->gridConfig->classId)->getName())
            . "\\Listing";

        $this->listing = new $className();
        $this->listing->setCondition($this->condition);
        $this->listing->addConditionParam(
            "o_path LIKE ?",
            rtrim($objectsFolder->getFullPath(), "/") . '/%',
            "AND"
        );
        $this->listing->addConditionParam("o_classId = ?", $this->gridConfig->classId, "AND");

        if ($this->changesFromTimestamp) {
            $this->listing->addConditionParam("o_modificationDate >= ?", $this->changesFromTimestamp, "AND");
        }
        $this->listing->setUnpublished(true);
        $this->process->setMessage("Counting objects to export");
        $this->process->save();
        $this->process->setTotal($this->listing->getCount());
        $this->process->save();
    }

    /**
     * @return array
     */
    protected function getObjectIds(): array
    {
        $objectIds = [];
        for($i = 0; $i <= $this->process->getTotal(); $i = $i + self::INTERNAL_BATCH_SIZE) {
            $this->listing->setOffset($i);
            $this->listing->setLimit(self::INTERNAL_BATCH_SIZE);
            $objectIds[] = $this->listing->loadIdList();
        }

        return $objectIds;
    }

    /**
     * @return array
     */
    protected function prepareFields(): array
    {
        /** @var GridConfig $fieldsRaw */
        $fieldsRaw = json_decode($this->gridConfig->getConfig())->columns;

        $this->setHelperColumnsInSession($fieldsRaw);

        $fields = [];
        foreach ($fieldsRaw as $item) {
            $fields[] = $item->name;
        }
        return $fields;
    }

    /**
     * @param array $fieldsRaw
     * @return void
     */
    protected function setHelperColumnsInSession($fieldsRaw): void
    {
        $helperColumns = [];
        foreach ($fieldsRaw as $key => $field) {
            if (strpos($key, '#') === 0) {
                $helperColumns[$field->name] = $field->fieldConfig;
            }
        }

        Tool\Session::useSession(function (AttributeBagInterface $session) use ($helperColumns) {
            $existingColumns = $session->get('helpercolumns', []);
            $helperColumns = array_merge($helperColumns, $existingColumns);
            $session->set('helpercolumns', $helperColumns);
        }, 'pimcore_gridconfig');
    }

    /**
     * @return void
     */
    protected function saveFileInAssets(array $filenames): void
    {
        $assetFolder = $this->assetFolder;
        $content = "";
        $separator = "\r\n";
        $firstFile = true;
        foreach ($filenames as $filename) {
            $tmpFile = PIMCORE_SYSTEM_TEMP_DIRECTORY . "/" . $filename . ".csv";
            $fileContent = file_get_contents($tmpFile);
            if (!$firstFile) {
                $content .= preg_replace('/^.+\n/', '', $fileContent);
            } else {
                $content .= $fileContent;
            }
            unlink($tmpFile);
            $firstFile = false;
        }

        if($this->input->getOption('divide_file')) {
            $line = strtok($content, $separator);
            $header = $line;
            $counter = 0;
            $fileCounter = 0;
            $subContent = "";
            while ($line !== false) {
                $line = strtok( $separator );
                $subContent .= $line . "\r\n";
                $counter++;
                if ($counter % $this->input->getOption('divide_file') == 0) {
                    $assetFile = $this->prepareAssetFile($assetFolder, $fileCounter);
                    $assetFile->setData($header . "\r\n" . $subContent);
                    $assetFile->save();
                    $subContent = "";
                    $fileCounter++;
                }
            }

            if (strlen($subContent)) {
                $assetFile = $this->prepareAssetFile($assetFolder, $fileCounter);
                $assetFile->setData($header . "\r\n" . $subContent);
                $assetFile->save();
            }
        } else {
            $assetFile = $this->prepareAssetFile($assetFolder);
            $assetFile->setData($content);
            $assetFile->save();
        }

    }

    /**
     * @param string $assetFolder
     * @return Asset
     */
    protected function prepareAssetFile($assetFolder, ?int $index = null): Asset
    {
        $assetFile = new Asset();

        try {
            $assetFile->setParent(Asset\Service::createFolderByPath($assetFolder));
        } catch (\Exception $ex) {
        }

        if ($index) {
            $filename = $this->fileName . "-" . $index;
        } else {
            $filename = $this->fileName;
        }


        $assetFile->setFilename($filename . ".csv");

        return $assetFile;
    }

    /**
     *
     */
    private function updateSettingsDate(): void
    {
        if ($this->onlyChanges) {
            $settings = $this->getExportSetting();
            $settings->setData(strftime("%Y-%m-%d %T", $this->importStartTimestamp));
            $settings->save();
        }
    }
}
