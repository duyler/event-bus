<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract;

interface ActionSubstitutionInterface
{
    public function addResultSubstitutions(string $actionId, array $substitutions): void;
    public function addHandlerSubstitution(string $actionId, string $handlerSubstitution): void;
    public function isSubstituteHandler(string $actionId): bool;
    public function getSubstituteHandler(string $actionId): string;
    public function isSubstituteResult(string $actionId): bool;
    public function getSubstituteResult(string $actionId): array;
}
