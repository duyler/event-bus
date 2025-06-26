<?php

declare(strict_types=1);

namespace Duyler\EventBus\Build;

readonly class Type
{
    public function __construct(
        public string $type,
        public bool $typeCollection = false,
    ) {}

    public static function of(string $type): self
    {
        return new self($type);
    }

    public static function collectionOf(string $type): self
    {
        return new self($type, true);
    }
}
