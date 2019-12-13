<?php
/**
 * @date      08/01/18 11:29
 * @author    Anna Zavodian <azavodian@divante.pl>
 * @copyright Copyright (c) 2017 Divante Ltd. (https://divante.co)
 */

namespace Divante\ScheduledExportBundle\Export;

use AppBundle\Util\StringWebsiteSettings;
use Pimcore\Bundle\AdminBundle\Controller\Admin\DataObject\DataObjectHelperController;
use Pimcore\Localization\LocaleService;
use Pimcore\Logger;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject\Folder;
use Pimcore\Model\GridConfig;
use Pimcore\Tool;
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
    const SETTINGS = '{"enableInheritance":true,"delimiter":";"}';

    const WS_NAME = 'Last_Scheduled_Export_Date';

    private $gridConfig;
    private $objectsFolder;
    private $assetFolder;
    private $condition;
    private $fileName;
    private $timestamp;
    private $timestampFormat;
    private $importStartTimestamp;
    private $container;

    /** @var bool $onlyChanges */
    private $onlyChanges;

    /** @var int $changesFromTimestamp */
    private $changesFromTimestamp;

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
        string $gridConfig,
        string $objectsFolder,
        string $assetFolder,
        $container,
        string $condition = null,
        string $fileName = null,
        string $timestamp = "0",
        string $onlyChanges = "0",
        string $timestampFormat = ""
    ) {
        $this->setTimestamp($timestamp);
        $this->setGridConfig($gridConfig);
        $this->setOnlyChanges($onlyChanges);
        $this->setObjectsFolder($objectsFolder);
        $this->setAssetFolder($assetFolder);
        $this->setCondition($condition);
        $this->setTimestampFormat($timestampFormat);
        $this->setFilename(\Pimcore\File::getValidFilename($fileName));
        $this->setContainer($container);
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
        $settings = new StringWebsiteSettings($this->gridConfig->getId() . "_" . self::WS_NAME);
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
        $request = $this->prepareRequest();

        if (!count($request->request->get('ids'))) {
            return;
        }

        $localeService = new LocaleService();
        $controller = new DataObjectHelperController();
        $controller->setContainer($this->container);
        $controller->doExportAction($request, $localeService);

        $this->saveFileInAssets();
        $this->updateSettingsDate();
    }

    /**
     * @return Request
     */
    protected function prepareRequest(): Request
    {
        $request = Request::createFromGlobals();

        $request->request->set('fileHandle', $this->fileName);
        $request->request->set('ids', $this->prepareObjectIds());
        $request->request->set('settings', self::SETTINGS);
        $request->request->set('classId', $this->gridConfig->classId);
        $request->request->set('initial', '1');
        $request->request->set('fields', $this->prepareFields());
        $request->request->set('language', 'en_GB');

        return $request;
    }

    /**
     * @return array
     */
    protected function prepareObjectIds(): array
    {
        $objectsFolder = Folder::getByPath($this->objectsFolder);

        $objectsList = new \Pimcore\Model\DataObject\Listing();
        $objectsList->setCondition($this->condition);
        $objectsList->addConditionParam(
            "o_path LIKE ?",
            rtrim($objectsFolder->getFullPath(), "/") . '/%',
            "AND"
        );
        $objectsList->addConditionParam("o_classId = ?", $this->gridConfig->classId, "AND");

        if ($this->changesFromTimestamp) {
            $objectsList->addConditionParam("o_modificationDate >= ?", $this->changesFromTimestamp, "AND");
        }
        $objectsList->setUnpublished(true);
        return $objectsList->loadIdList();
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
    protected function saveFileInAssets(): void
    {
        $assetFolder = $this->assetFolder;
        $assetFile = Asset::getByPath($assetFolder . "/" . $this->fileName . ".csv");
        if (!$assetFile) {
            $assetFile = $this->prepareAssetFile($assetFolder);
        }

        $tmpFile = PIMCORE_SYSTEM_TEMP_DIRECTORY . "/" . $this->fileName . ".csv";
        $assetFile->setData(file_get_contents($tmpFile));
        unlink($tmpFile);
        try {
            $assetFile->save();
        } catch (\Exception $e) {
            Logger::err("Couldn't save asset with id " . $assetFile->getId());
        }
    }

    /**
     * @param string $assetFolder
     * @return Asset
     */
    protected function prepareAssetFile($assetFolder): Asset
    {
        $assetFile = new Asset();

        try {
            $assetFile->setParent(Asset\Service::createFolderByPath($assetFolder));
        } catch (\Exception $ex) {
        }

        $assetFile->setFilename($this->fileName . ".csv");

        return $assetFile;
    }

    /**
     *
     */
    private function updateSettingsDate(): void
    {
        if ($this->onlyChanges) {
            $settings = new StringWebsiteSettings(
                $this->gridConfig->getId() .
                "_" . Folder::getByPath($this->objectsFolder)->getId() .
                "_" . self::WS_NAME
            );
            $settings->setData(strftime("%Y-%m-%d %T", $this->importStartTimestamp));
        }
    }
}
