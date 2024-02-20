<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract;

interface ActionSubstitutionInterface
{
    /** @param array<string, object> $substitutions  */
    public function addResultSubstitutions(string $actionId, array $substitutions): void;

    public function addHandlerSubstitution(string $actionId, string $handlerSubstitution): void;

    public function isSubstituteHandler(string $actionId): bool;

    public function getSubstituteHandler(string $actionId): string;

    public function isSubstituteResult(string $actionId): bool;

    /** @return array<string, object> */
    public function getSubstituteResult(string $actionId): array;
}
