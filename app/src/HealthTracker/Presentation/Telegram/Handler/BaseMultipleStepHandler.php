<?php

declare(strict_types=1);

namespace App\HealthTracker\Presentation\Telegram\Handler;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;

abstract class BaseMultipleStepHandler
{
    public function __construct(
        protected readonly CacheItemPoolInterface $cache,
        protected readonly int $lifetime = 0,
    ) {}

    abstract public function createData(): MultipleStepHandlerDataInterface;

    abstract public function getDataClassName(): string;

    abstract protected function getPrefixStep(): string;

    abstract protected function getPrefixData(): string;

    /**
     * @param string $id
     * @return bool
     * @throws InvalidArgumentException
     */
    public function hasData(string $id): bool
    {
        $key = $this->getKey($this->getPrefixStep(), $id);

        return $this->cache->hasItem($key);
    }

    /**
     * @param string $id
     * @return void
     * @throws InvalidArgumentException
     */
    public function clearData(string $id): void
    {
        $stepKey = $this->getKey($this->getPrefixStep(), $id);
        $dataKey = $this->getKey($this->getPrefixData(), $id);

        $this->cache->deleteItems([$stepKey, $dataKey]);
    }

    /**
     * @param string $id
     * @return int
     * @throws InvalidArgumentException
     */
    public function getCurrentStep(string $id): int
    {
        $key = $this->getKey($this->getPrefixStep(), $id);

        if (!$this->cache->hasItem($key)) {
            return 0;
        }

        $item = $this->cache->getItem($key);

        return (int)$item->get();
    }

    /**
     * @param string $id
     * @param int $step
     * @return void
     * @throws InvalidArgumentException
     */
    public function setCurrentStep(string $id, int $step): void
    {
        $key = $this->getKey($this->getPrefixStep(), $id);

        $item = $this->cache->getItem($key);
        $item->set($step);

        if ($this->lifetime > 0) {
            $item->expiresAfter($this->lifetime);
        }

        $this->cache->save($item);
    }

    /**
     * @param string $id
     * @return MultipleStepHandlerDataInterface
     * @throws InvalidArgumentException
     */
    public function getData(string $id): MultipleStepHandlerDataInterface
    {
        $key = $this->getKey($this->getPrefixData(), $id);

        if (!$this->cache->hasItem($key)) {
            return $this->createData();
        }

        $item = $this->cache->getItem($key);

        return $item->get();
    }

    /**
     * @param string $id
     * @param MultipleStepHandlerDataInterface $data
     * @return void
     * @throws InvalidArgumentException
     * @throws \InvalidArgumentException
     */
    public function setData(string $id, MultipleStepHandlerDataInterface $data): void
    {
        $dataClassName = $this->getDataClassName();

        if (!$data instanceof $dataClassName) {
            throw new \InvalidArgumentException('Переданы некорректные данные');
        }

        $key = $this->getKey($this->getPrefixData(), $id);

        $item = $this->cache->getItem($key);
        $item->set($data);

        if ($this->lifetime > 0) {
            $item->expiresAfter($this->lifetime);
        }

        $this->cache->save($item);
    }

    protected function getKey(string $prefix, string $id): string
    {
        return sprintf('%s%s', $prefix, $id);
    }
}
