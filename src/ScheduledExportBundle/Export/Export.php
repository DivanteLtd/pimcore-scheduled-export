<?php
/**
 * @date      08/01/18 11:29
 * @author    Anna Zavodian <azavodian@divante.pl>
 * @copyright Copyright (c) 2017 Divante Ltd. (https://divante.co)
 */

declare(strict_types=1);

namespace Divante\ScheduledExportBundle\Export;

use Divante\ScheduledExportBundle\Event\BatchExportedEvent;
use Divante\ScheduledExportBundle\Event\ScheduledExportSavedEvent;
use Divante\ScheduledExportBundle\Exceptions\ScheduledExportException;
use Divante\ScheduledExportBundle\Model\ScheduledExportRegistry;
use Elements\Bundle\ProcessManagerBundle\Model\MonitoringItem;
use Exception;
use Pimcore;
use Pimcore\Bundle\AdminBundle\Controller\Admin\DataObject\DataObjectHelperController;
use Pimcore\File;
use Pimcore\Localization\LocaleService;
use Pimcore\Log\ApplicationLogger;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject\ClassDefinition;
use Pimcore\Model\DataObject\Folder;
use Pimcore\Model\DataObject\Listing;
use Pimcore\Model\GridConfig;
use Pimcore\Tool;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;

/**
 * Class Export
 * @package Divante\ScheduledExportBundle\Service
 * @SuppressWarnings(PHPMD)
 */
class Export
{
    private const SETTINGS = '{"enableInheritance":true,"delimiter":"%delimiter%"}';

    private const INTERNAL_BATCH_SIZE = 1000;

    /** @var MonitoringItem */
    protected $monitoringItem;

    /** @var ApplicationLogger */
    protected $logger;

    /** @var ContainerInterface */
    private $container;

    /** @var array */
    protected $callbackSettings = [];

    private ?GridConfig $gridConfig;
    private $objectsFolder;
    private $assetFolder;
    private $condition;
    private $fileName;

    /** @var bool */
    private $timestamp;

    private $timestampFormat;
    private $importStartTimestamp;
    private $delimiter;
    private $types;

    /** @var Listing $listing */
    private $listing;

    /** @var bool $onlyChanges */
    private $onlyChanges;

    /** @var int $changesFromTimestamp */
    private $changesFromTimestamp;

    /** @var DataObjectHelperController $controller */
    private $controller;

    /** @var int */
    private $totalToExport = 0;

    /**
     * @throws Exception
     */
    public function __construct(
        MonitoringItem $monitoringItem,
        ContainerInterface $container,
        Pimcore\Serializer\Serializer $serializer
    ) {
        $this->setMonitoringItem($monitoringItem);
        $this->setLogger($monitoringItem->getLogger());
        $this->setCallbackSettings($monitoringItem->getCallbackSettings());
        $this->setContainer($container);


        $this->assignCallbackSettings();

        $this->controller = new DataObjectHelperController();
        $this->controller->setContainer($this->container);
        $this->controller->setPimcoreSerializer($serializer);

        $this->logger->info(sprintf("Scheduled Export (%s) - %s", $this->objectsFolder, $this->gridConfig->getName()));
    }

    public function setMonitoringItem(MonitoringItem $monitoringItem): Export
    {
        $this->monitoringItem = $monitoringItem;

        return $this;
    }

    public function getLogger(): ApplicationLogger
    {
        return $this->logger;
    }

    public function setLogger(ApplicationLogger $logger): Export
    {
        $this->logger = $logger;

        return $this;
    }

    public function getCallbackSettings(): array
    {
        return $this->callbackSettings;
    }

    public function setCallbackSettings(array $callbackSettings): Export
    {
        $this->callbackSettings = $callbackSettings;

        return $this;
    }

    /**
     * @throws Exception
     */
    public function execute(): void
    {
        if (!empty($this->getCallbackSettings()['OBJECT_IDS'])) {
            $objectIds = explode(',', $this->getCallbackSettings()['OBJECT_IDS']);
            $objectIds = array_map('intval', $objectIds);
            $objectIds = array_chunk($objectIds, self::INTERNAL_BATCH_SIZE);
        } else {
            $this->prepareListing();
            $objectIds = $this->getObjectIds();
        }

        $filenames = $this->exportToFilenames($objectIds);

        $status = $this->monitoringItem::STATUS_FINISHED;
        $statusMsg = 'Finished Export: ' . implode(' / ', $filenames);

        if (!empty($filenames)) {
            try {
                $this->saveFileInAssets($filenames);
                $this->monitoringItem->setWorkloadCompleted();
                $this->updateExportRegistry();
            } catch (Exception $exception) {
                $status = $this->monitoringItem::STATUS_FAILED;
                $statusMsg = sprintf("Error - %s", $exception->getMessage());
            }
        }

        $this->monitoringItem->setStatus($status)->setMessage($statusMsg);
    }

    /**
     * @throws Exception
     */
    private function assignCallbackSettings(): void
    {
        $this->logger->info(var_export($this->getCallbackSettings(), true));
        $this->setTimestamp();
        $this->setGridConfig();
        $this->setObjectsFolder();
        $this->setOnlyChanges();
        $this->setAssetFolder();
        $this->setCondition();
        $this->setTimestampFormat();
        $this->setFilename();
        $this->setDelimiter();
        $this->types = str_replace(' ', '', (string) $this->getCallbackSettings()['TYPES']);
    }

    /**
     * @return ScheduledExportRegistry
     * @throws Exception
     */
    public function getExportRegistry() : ScheduledExportRegistry
    {
        $adaptedGridConfigId = sprintf(
            '%s_%s',
            $this->gridConfig->getId(),
            Folder::getByPath($this->objectsFolder)->getId() ?? 0
        );

        try {
            $exportRegistry = ScheduledExportRegistry::getByGridConfigId($adaptedGridConfigId);
        } catch (Exception $exception) {
            $exportRegistry = new ScheduledExportRegistry();
            $exportRegistry->setGridConfigId($adaptedGridConfigId);
            $exportRegistry->save();
        }

        return $exportRegistry;
    }

    public function setTimestamp(): void
    {
        $this->timestamp = !empty($this->getCallbackSettings()['ADD_TIMESTAMP']);
    }

    /**
     * @throws Exception
     */
    public function setGridConfig(): void
    {
        $callbackSettings = $this->getCallbackSettings();
        $this->gridConfig = !empty($callbackSettings['GRID_CONFIG']) ? GridConfig::getById($callbackSettings['GRID_CONFIG']) : null;
    }

    /**
     * @throws Exception
     */
    public function setOnlyChanges(): void
    {
        $exportRegistry = $this->getExportRegistry();

        if (!empty($this->getCallbackSettings()['ONLY_CHANGES'])) {
            $this->onlyChanges = true;
            $this->changesFromTimestamp = !empty($exportRegistry->getData()) ? strtotime($exportRegistry->getData()) : time();
        } else {
            $this->onlyChanges = false;
            $this->changesFromTimestamp = 0;
        }

        $this->importStartTimestamp = time();
    }

    public function setObjectsFolder(): void
    {
        $this->objectsFolder = $this->getCallbackSettings()['OBJECTS_FOLDER'];
    }

    public function setAssetFolder(): void
    {
        $this->assetFolder = $this->getCallbackSettings()['ASSET_FOLDER'];
    }

    public function setDelimiter(): void
    {
        $delimiter = $this->getCallbackSettings()['DELIMITER'];

        if (empty($delimiter)) {
            $delimiter = ';';
        }

        $this->delimiter = $delimiter;
    }

    public function setCondition(): void
    {
        $this->condition = (string) $this->getCallbackSettings()['CONDITION'];
    }

    public function setFileName(): void
    {
        $fileName = File::getValidFilename((string) $this->getCallbackSettings()['ASSET_FILENAME']);

        if ($this->timestamp) {
            $format = empty($this->timestampFormat) ? "-%s" : $this->timestampFormat;

            $fileName .= strftime($format);
        }

        $this->fileName = $fileName;
    }

    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    public function getTimestampFormat(): string
    {
        return $this->timestampFormat;
    }

    public function setTimestampFormat(): void
    {
        $this->timestampFormat = (string) $this->getCallbackSettings()['TIMESTAMP'];
    }

    protected function prepareRequest(array $objectIds, string $filename): Request
    {
        $request = Request::createFromGlobals();

        $request->request->set('fileHandle', $filename);
        $request->request->set('ids', $objectIds);
        $request->request->set('settings', str_replace('%delimiter%', $this->delimiter, self::SETTINGS));
        $request->request->set('classId', $this->gridConfig->getClassId());
        $request->request->set('initial', '1');
        $request->request->set('fields', $this->prepareFields());
        $request->request->set('language', 'en_GB');

        return $request;
    }

    protected function prepareListing(): void
    {
        $objectsFolder = Folder::getByPath($this->objectsFolder);
        $className = "\\Pimcore\\Model\\DataObject\\"
            . ucfirst(ClassDefinition::getById($this->gridConfig->getClassId())->getName())
            . "\\Listing";

        $this->listing = new $className();
        $this->listing->setCondition($this->condition);
        $this->listing->addConditionParam(
            'o_path LIKE ?',
            rtrim($objectsFolder->getFullPath(), '/') . '/%'
        );
        $this->listing->addConditionParam('o_classId = ?', $this->gridConfig->getClassId());

        if ($this->changesFromTimestamp) {
            $this->listing->addConditionParam('o_modificationDate >= ?', $this->changesFromTimestamp);
        }
        $this->listing->setUnpublished(true);
        if ($this->types) {
            $this->listing->setObjectTypes(explode(',', $this->types));
        }

        $this->totalToExport = $this->listing->getCount();

        $this->logger->info('Objects to export: ' . $this->totalToExport);
    }

    protected function getObjectIds(): array
    {
        $objectIds = [];
        for ($i = 0; $i <= $this->totalToExport; $i += self::INTERNAL_BATCH_SIZE) {
            $this->listing->setOffset($i);
            $this->listing->setLimit(self::INTERNAL_BATCH_SIZE);
            $objectIds[] = $this->listing->loadIdList();
        }

        return $objectIds;
    }

    protected function prepareFields(): array
    {
        /** @var GridConfig $fieldsRaw */
        $fieldsRaw = json_decode($this->gridConfig->getConfig(), false)->columns;

        $this->setHelperColumnsInSession($fieldsRaw);

        $fields = [];
        foreach ($fieldsRaw as $item) {
            $fields[] = $item->name;
        }
        return $fields;
    }

    protected function setHelperColumnsInSession($fieldsRaw): void
    {
        $helperColumns = [];
        foreach ($fieldsRaw as $key => $field) {
            if (strpos($key, '#') === 0) {
                $helperColumns[$field->name] = $field->fieldConfig;
            }
        }

        Tool\Session::useSession(
            function (AttributeBagInterface $session) use ($helperColumns) {
                $existingColumns = $session->get('helpercolumns', []);
                $helperColumns = array_merge($helperColumns, $existingColumns);
                $session->set('helpercolumns', $helperColumns);
            },
            'pimcore_gridconfig'
        );
    }

    /**
     * @throws Exception
     */
    protected function saveFileInAssets(array $filenames): void
    {
        $assetFolder = $this->assetFolder;
        $content = '';
        $separator = "\r\n";
        $firstFile = true;

        $storage = Tool\Storage::get('temp');

        foreach ($filenames as $filename) {
            $tmpFile = PIMCORE_SYSTEM_TEMP_DIRECTORY . '/' . $filename . '.csv';
            $fileContent = '';
            try {
                $fileContent = $storage->read($filename . '.csv');
            } catch (\Exception $e) {
            }

            if (!$firstFile) {
                $content .= $separator . preg_replace('/^.+\n/', '', $fileContent);
            } else {
                $content .= $fileContent;
            }
            unlink($tmpFile);
            $firstFile = false;
        }

        if (!empty($this->getCallbackSettings()['DIVIDE_FILE'])) {
            $divide = (int) $this->getCallbackSettings()['DIVIDE_FILE'];
            $rows = explode($separator, $content);
            $header = array_shift($rows);

            $counter = 0;
            $fileCounter = 0;
            $subContent = '';
            foreach ($rows as $row) {
                $subContent .= $row . "\r\n";
                if (++$counter % $divide === 0) {
                    $this->saveAsset($assetFolder, $fileCounter, $header, $subContent);
                    $subContent = '';
                    $fileCounter++;
                }
            }

            if ($subContent !== '') {
                $this->saveAsset($assetFolder, $fileCounter, $header, $subContent);
            }
        } else {
            $this->saveAsset($assetFolder, null, null, $content);
        }
    }

    /**
     * @throws Exception
     */
    protected function prepareAssetFile(string $assetFolder, ?int $index = null): Asset
    {
        if ($index) {
            $filename = $this->fileName . '-' . $index;
        } else {
            $filename = $this->fileName;
        }

        $filename .= '.csv';

        if (!$this->timestamp) {
            $assetFile = Asset::getByPath(Asset\Service::createFolderByPath($assetFolder) . '/' . $filename);
            if (!$assetFile) {
                $assetFile = new Asset();
                $assetFile->setParent(Asset\Service::createFolderByPath($assetFolder));
                $assetFile->setFilename($filename);
            }
        } else {
            $assetFile = new Asset();
            $assetFile->setParent(Asset\Service::createFolderByPath($assetFolder));
            $assetFile->setFilename($filename);
        }

        return $assetFile;
    }

    /**
     * @throws Exception
     */
    private function updateExportRegistry(): void
    {
        if ($this->onlyChanges) {
            $exportRegistry = $this->getExportRegistry();
            $exportRegistry->setData(strftime("%Y-%m-%d %T", $this->importStartTimestamp));
            $exportRegistry->save();
        }
    }

    /**
     * @throws Exception
     */
    protected function exportToFilenames(array $objectIds): array
    {
        $filenames = [];
        $localeService = new LocaleService();
        /** @var EventDispatcher $dispatcher */
        $dispatcher = $this->container->get('event_dispatcher');

        if ($dispatcher === null) {
            $this->logger->error('Event Dispatch is null - could not proceed!');
            throw new ScheduledExportException('Event Dispatch is null - could not proceed!');
        }

        $this->monitoringItem->setTotalSteps(count($objectIds))->save();

        $count = 0;
        $storage = Tool\Storage::get('temp');

        foreach ($objectIds as $objectIdBatch) {
            $count++;
            $filename = uniqid('', true);
            $request = $this->prepareRequest($objectIdBatch, $filename);
            if (!count($request->request->get('ids'))) {
                break;
            }

            $storage->write($filename . '.csv', '');

            $this->controller->doExportAction($request, $localeService, $dispatcher);
            $event = new BatchExportedEvent($objectIdBatch ?? []);
            $dispatcher->dispatch($event, BatchExportedEvent::NAME);

            $filenames[] = $filename;

            $batchCount = count($objectIdBatch);

            $this->monitoringItem->setCurrentStep($count)->setMessage('Processing ' . $batchCount . ' export objects')->save();

            if ($this->monitoringItem->getStatus() === MonitoringItem::STATUS_FAILED) {
                foreach ($filenames as $filename) {
                    $tmpFile = PIMCORE_SYSTEM_TEMP_DIRECTORY . '/' . $filename . '.csv';
                    unlink($tmpFile);
                }
                break;
            }

            Pimcore::collectGarbage();
        }

        return $filenames;
    }

    /**
     * @param $assetFolder
     * @param int|null $fileCounter
     * @param string|null $header
     * @param string $content
     * @throws Exception
     */
    protected function saveAsset($assetFolder, ?int $fileCounter, ?string $header, string $content): void
    {
        $assetFile = $this->prepareAssetFile($assetFolder, $fileCounter);

        $data = '';
        if (!empty($this->getCallbackSettings()['ADD_UTF_BOM'])) {
            $data = chr(0xEF) . chr(0xBB) . chr(0xBF);
        }

        if ($header) {
            $data .= $header . "\r\n" . $content;
        } else {
            $data .= $content;
        }

        $assetFile->setData($data);

        $assetFilename = Asset\Service::getUniqueKey($assetFile);
        $assetFile->setFilename($assetFilename);

        try {
            $assetFile->save([
                'versionNote' => sprintf(
                    "Scheduled Export on folder (%s), gridconfig -  %s",
                    $this->objectsFolder,
                    $this->gridConfig->getName()
                )
            ]);
        } catch (\Exception $e) {
            \Pimcore\Log\ApplicationLogger::getInstance()->warning('Error on saving Asset "' . $assetFilename . '" : ' . $e->getMessage());
        }

        $event = new ScheduledExportSavedEvent($assetFilename);

        /** @var TraceableEventDispatcher $eventDispatcher */
        $eventDispatcher = $this->container->get('event_dispatcher');
        $eventDispatcher?->dispatch($event, ScheduledExportSavedEvent::NAME);
    }
}
