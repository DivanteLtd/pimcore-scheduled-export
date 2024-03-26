<?php

declare(strict_types=1);

namespace Divante\ScheduledExportBundle\Model;

use Divante\ScheduledExportBundle\Model\ScheduledExportRegistry\Dao;
use Exception;
use Pimcore\Cache;
use Pimcore\Model\AbstractModel;

/**
 * @method Dao getDao()
 * @method void save()
 */
class ScheduledExportRegistry extends AbstractModel
{
    protected const WS_NAME = 'Scheduled_Export_Registry';

    protected ?int $id = null;

    protected string $gridConfigId;

    protected mixed $data = null;

    protected static array $nameIdMappingCache = [];


    public function __construct(string $gridConfigId = null, $data = null)
    {
        if (!empty($gridConfigId)) {
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

    public function getGridConfigId(): string
    {
        return $this->gridConfigId;
    }

    public function setGridConfigId(string $gridConfigId): self
    {
        $this->gridConfigId = $gridConfigId;

        return $this;
    }

    public function setData(?string $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getData(): ?string
    {
        return $this->data;
    }


    protected static function getCacheKey(string $gridConfigId): string
    {
        return $gridConfigId . '~~~' . self::WS_NAME;
    }

    public function clearDependentCache(): void
    {
        Cache::clearTag('scheduled_export_exports');
    }


    public static function getById(int $id): ?self
    {
        $cacheKey = 'scheduled_export_exports_' . $id;

        try {
            $export = Cache\RuntimeCache::get($cacheKey);
            if (!$export) {
                throw new Exception(sprintf('Scheduled export with ID `%s` does not exist.', $id));
            }
        } catch (Exception $e) {
            try {
                $export = new self();
                $export->getDao()->getById($id);
                Cache\RuntimeCache::set($cacheKey, $export);
            } catch (Exception $e) {
                return null;
            }
        }

        return $export;
    }

    /**
     * @throws Exception
     */
    public static function getByGridConfigId(string $gridConfigId): ?self
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
