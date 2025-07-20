<?php

declare(strict_types=1);

namespace App\HealthTracker\Infrastructure\Telegram\Handler;

use App\HealthTracker\Infrastructure\Telegram\DTO\AcquaintanceUserData;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;

class AcquaintanceHandler
{
    public const string PREFIX_USER = 'acquaintance_user_';
    public const string PREFIX_STEP = 'acquaintance_step_';

    public function __construct(
        private readonly CacheItemPoolInterface $cache,
        private readonly int $lifetime = 0,
    ) {}

    /**
     * @param string $id
     * @return bool
     * @throws InvalidArgumentException
     */
    public function hasData(string $id): bool
    {
        $key = $this->getKey(self::PREFIX_STEP, $id);

        return $this->cache->hasItem($key);
    }

    /**
     * @param string $id
     * @return void
     * @throws InvalidArgumentException
     */
    public function clearData(string $id): void
    {
        $stepKey = $this->getKey(self::PREFIX_STEP, $id);
        $userKey = $this->getKey(self::PREFIX_USER, $id);

        $this->cache->deleteItems([$stepKey, $userKey]);
    }

    /**
     * @param string $id
     * @return int
     * @throws InvalidArgumentException
     */
    public function getCurrentStep(string $id): int
    {
        $key = $this->getKey(self::PREFIX_STEP, $id);

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
        $key = $this->getKey(self::PREFIX_STEP, $id);

        $item = $this->cache->getItem($key);
        $item->set($step);

        if ($this->lifetime > 0) {
            $item->expiresAfter($this->lifetime);
        }

        $this->cache->save($item);
    }

    /**
     * @param string $id
     * @return AcquaintanceUserData
     * @throws InvalidArgumentException
     */
    public function getUserData(string $id): AcquaintanceUserData
    {
        $key = $this->getKey(self::PREFIX_USER, $id);

        if (!$this->cache->hasItem($key)) {
            return $this->createUserData();
        }

        $item = $this->cache->getItem($key);

        return $item->get();
    }

    /**
     * @param string $id
     * @param AcquaintanceUserData $userData
     * @return void
     * @throws InvalidArgumentException
     */
    public function setUserData(string $id, AcquaintanceUserData $userData): void
    {
        $key = $this->getKey(self::PREFIX_USER, $id);

        $item = $this->cache->getItem($key);
        $item->set($userData);

        if ($this->lifetime > 0) {
            $item->expiresAfter($this->lifetime);
        }

        $this->cache->save($item);
    }

    public function createUserData(): AcquaintanceUserData
    {
        return new AcquaintanceUserData();
    }

    private function getKey(string $prefix, string $id): string
    {
        return sprintf('%s%s', $prefix, $id);
    }
}
