<?php declare(strict_types=1);

namespace App\Service;

use DateTime;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\CacheInterface;

final class BruteForceService
{
    private int $limit;
    private int $interval;
    private string $client;

    private DateTime $expireTime;

    public function __construct(
        private readonly CacheInterface $cache
    )
    {
    }

    public function setup(
        string $client,
        int $limit,
        int $interval
    ): self
    {
        $this->client = $client;
        $this->limit = $limit;
        $this->interval = $interval;

        $this->addClientToList();

        return $this;
    }

    private function addClientToList(): void
    {
        $this->cache->get('brute_force_count_' . $this->client, function (ItemInterface $item) {
           $item->expiresAfter($this->interval);

           return 0;
        });
    }

    public function increaseCounter(
        int $count = 1
    ): bool
    {
        $currentStatus = $this->cache->get('brute_force_count_' . $this->client, function () {});

        if ($currentStatus > $this->limit || ($currentStatus + $count) > $this->limit) {
            return false;
        }

        $this->cache->delete('brute_force_count_' . $this->client);

        $this->cache->get('brute_force_count_' . $this->client, function (ItemInterface $item) use ($currentStatus, $count) {
            $item->expiresAfter($this->interval);

            $newStatus = $currentStatus + $count;

            if ($newStatus === $this->limit) {
                $this->cache->delete('brute_force_time_' . $this->client);

                $this->cache->get('brute_force_time_' . $this->client, function (ItemInterface $item) {
                    $item->expiresAfter($this->interval);

                    return $this->expireTime = new DateTime('+' . $this->interval . ' seconds');
                });
            }

            return $newStatus;
        });

        return true;
    }

    public function hasClientAttemptsLeft(): bool
    {
        $status = $this->cache->get('brute_force_count_' . $this->client, function () {});

        return $status !== $this->limit;
    }

    public function getCurrentStatus(): int
    {
        return $this->cache->get('brute_force_count_' . $this->client, function () {});
    }

    public function getTimeToWait(): int
    {
        $this->expireTime = $this->cache->get('brute_force_time_' . $this->client, function () {});

        return $this->expireTime->getTimestamp();
    }

    public function remove(): void
    {
        $this->cache->delete('brute_force_count_'. $this->client);
        $this->cache->delete('brute_force_time_' . $this->client);
    }


}
