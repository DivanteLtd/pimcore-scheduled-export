<?php

declare(strict_types=1);

namespace Divante\ScheduledExportBundle\Model;

use Pimcore\Model\Element\ElementInterface;
use Pimcore\Model\Element\Service;
use Pimcore\Model\AbstractModel;

/**
 * @method \Divante\ScheduledExportBundle\Model\ScheduledExportRegistry\Dao getDao()
 * @method void save()
 */
class ScheduledExportRegistry extends AbstractModel
{
    protected const WS_NAME = 'Scheduled_Export_Registry';

    /**
     * @var int
     */
    protected $id;

    /**
     * @var int
     */
    protected $gridConfigId;

    /**
     * @var mixed
     */
    protected $data;

    /**
     * @var array
     */
    protected static $nameIdMappingCache = [];


    public function __construct(int $gridConfigId = null, $data = null)
    {
        if (!empty($gridConfigId) && !empty($data)) {
            $this->setGridConfigId($gridConfigId);
            $this->setData($data);
        }
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getGridConfigId(): int
    {
        return $this->gridConfigId;
    }

    public function setGridConfigId(int $gridConfigId): self
    {
        $this->gridConfigId = $gridConfigId;

        return $this;
    }
    
    public function setData(string $data): self
    {
        $this->data = $data;

        return $this;
    }
    
    public function getData(): string
    {
        return $this->data;
    }


    protected static function getCacheKey(int $gridConfigId): string
    {
        return $gridConfigId . '~~~' . self::WS_NAME;
    }

    public function clearDependentCache()
    {
        \Pimcore\Cache::clearTag('scheduled_export_exports');
    }


    public static function getById(int $id): ?self
    {
        $cacheKey = 'scheduled_export_exports_' . $id;

        try {
            $export = \Pimcore\Cache\Runtime::get($cacheKey);
            if (!$export) {
                throw new \Exception(sprintf('Scheduled export with ID `%s` does not exist.', $id));
            }
        } catch (\Exception $e) {
            try {
                $export = new self();
                $export->getDao()->getById($id);
                \Pimcore\Cache\Runtime::set($cacheKey, $export);
            } catch (\Exception $e) {
                return null;
            }
        }

        return $export;
    }

    public static function getByGridConfigId(int $gridConfigId): ?self
    {
        $nameCacheKey = static::getCacheKey($gridConfigId);

        if (array_key_exists($nameCacheKey, self::$nameIdMappingCache)) {
            return self::getById(self::$nameIdMappingCache[$nameCacheKey]);
        }

        $export = new self();
        $export->getDao()->getByGridConfigId($gridConfigId);

        if ($export->getId() > 0) {
            self::$nameIdMappingCache[$nameCacheKey] = $export->getId();

            return self::getById($export->getId());
        }

        return $export;
    }

    public function delete(): void
    {
        $nameCacheKey = static::getCacheKey($this->getGridConfigId());

        if (array_key_exists($nameCacheKey, self::$nameIdMappingCache)) {
            unset(self::$nameIdMappingCache[$nameCacheKey]);
        }

        $this->getDao()->delete();
    }
}
