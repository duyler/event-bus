<?php

declare(strict_types=1);

namespace Duyler\EventBus\Build;

use Duyler\EventBus\Enum\ResultStatus;
use Duyler\EventBus\Formatter\IdFormatter;
use Override;
use Stringable;
use UnitEnum;

final readonly class Id implements Stringable
{
    public function __construct(
        public string $from,
    ) {}

    public static function from(string|UnitEnum $subject, ResultStatus $status): Id
    {
        return new Id(IdFormatter::toString($subject) . IdFormatter::DELIMITER . $status->value);
    }

    public static function success(string|UnitEnum $actionId): Id
    {
        return self::from($actionId, ResultStatus::Success);
    }

    public static function fail(string|UnitEnum $actionId): Id
    {
        return self::from($actionId, ResultStatus::Fail);
    }

    public function getSubject(): string
    {
        $lastDelimiterPos = strrpos($this->from, IdFormatter::DELIMITER);

        if (false === $lastDelimiterPos) {
            return $this->from;
        }

        return substr($this->from, 0, $lastDelimiterPos);
    }

    public function getStatus(): ?ResultStatus
    {
        $lastDelimiterPos = strrpos($this->from, IdFormatter::DELIMITER);

        if (false === $lastDelimiterPos) {
            return null;
        }

        $statusString = substr($this->from, $lastDelimiterPos + strlen(IdFormatter::DELIMITER));

        foreach (ResultStatus::cases() as $status) {
            if ($status->value === $statusString) {
                return $status;
            }
        }

        return null;
    }

    #[Override]
    public function __toString(): string
    {
        return $this->from;
    }
}
