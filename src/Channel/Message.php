<?php

declare(strict_types=1);

namespace Duyler\EventBus\Channel;

use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use Fiber;
use RuntimeException;

final class Message
{
    private mixed $payload = null;

    private string $tag = 'default';

    private DateTimeInterface $ttl;

    public function __construct(
        private readonly string $channel,
        private readonly Transfer $transfer,
    ) {
        /** @var DateInterval $interval */
        $interval = DateInterval::createFromDateString('1 minute');
        $this->ttl = (new DateTimeImmutable())->add($interval);
    }

    public function setPayload(mixed $payload, string $tag): self
    {
        $this->tag = $tag;
        $this->payload = $payload;
        return $this;
    }

    public function setTtl(string $ttl): self
    {
        /** @var DateInterval $interval */
        $interval = DateInterval::createFromDateString($ttl);
        $this->ttl = (new DateTimeImmutable())->add($interval);
        return $this;
    }

    public function getTtl(): DateTimeInterface
    {
        return $this->ttl;
    }

    public function getPayload(): mixed
    {
        return $this->payload;
    }

    public function getChannel(): string
    {
        return $this->channel;
    }

    public function getTag(): string
    {
        return $this->tag;
    }

    public function push(): void
    {
        if (null === $this->payload) {
            throw new RuntimeException('Message has no payload');
        }

        Fiber::suspend(function (): void {
            $this->transfer->push($this);
        });
    }
}
