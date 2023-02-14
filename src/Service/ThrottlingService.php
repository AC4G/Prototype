<?php declare(strict_types=1);

namespace App\Service;

use DateTime;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

final class ThrottlingService
{
    private int $limit;
    private int $interval;
    private string $client;

    public function __construct(
        private readonly FilesystemAdapter $cache
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
        $count = $this->cache->getItem('throttling_count_' . $this->client);

        if ($count->isHit()) {
            return;
        }

        $count
            ->set(0)
            ->expiresAfter($this->interval)
        ;

        $this->cache->save($count);
    }

    public function increaseCounter(
        int $count = 1
    ): bool
    {
        $currentStatus = $this->cache->getItem('throttling_count_' . $this->client);

        $status = $currentStatus->get();

        if ($status > $this->limit || ($status + $count) > $this->limit) {
            return false;
        }

        $newStatus = $status + $count;

        $currentStatus
            ->set($newStatus)
            ->expiresAfter($this->interval)
        ;

        $this->cache->save($currentStatus);

        if ($newStatus === $this->limit) {
            $timer = $this->cache->getItem('throttling_time_' . $this->client);

            $timer
                ->set([
                    'created' => new DateTime(),
                    'expires' => new DateTime('+' . $this->interval . ' seconds')
                ])
                ->expiresAfter($this->interval)
            ;

            $this->cache->save($timer);
        }

        return true;
    }

    public function hasClientAttemptsLeft(): bool
    {
        $status = $this->cache->getItem('throttling_count_' . $this->client);

        return $status->get() !== $this->limit;
    }

    public function getCurrentStatus(): int
    {
        return $this->cache->getItem('throttling_count_' . $this->client)->get();
    }

    public function getTimerList(): array
    {
        return $this->cache->getItem('throttling_time_' . $this->client)->get();
    }

    public function getStaticWaitTime(): int
    {
        $timer = $this->cache->getItem('throttling_time_' . $this->client)->get();

        $created = $timer['created'];
        $expires = $timer['expires'];

        return ($expires->getTimestamp() - $created->getTimestamp()) / 60;
    }

    public function getTimeToLive(): int
    {
        $timer = $this->cache->getItem('throttling_time_' . $this->client)->get();

        return ($timer['expires']->getTimestamp() - (new DateTime())->getTimestamp()) % 60;
    }

    public function remove(): void
    {
        $this->cache->delete('throttling_count_'. $this->client);
        $this->cache->delete('throttling_time_' . $this->client);
    }


}
