<?php

declare(strict_types=1);

namespace Duyler\EventBus\Action;

use Duyler\DependencyInjection\Attribute\Reset;
use Duyler\EventBus\Contract\ActionSubstitutionInterface;
use Duyler\EventBus\Dto\ActionHandlerSubstitution;
use Override;

#[Reset]
class ActionSubstitution implements ActionSubstitutionInterface
{
    /** @var array<string, array<string, object>> */
    private array $requiredResultSubstitutions = [];

    /** @var array<string, ActionHandlerSubstitution> */
    private array $handlerSubstitutions = [];

    #[Override]
    public function addResultSubstitutions(string $actionId, array $substitutions): void
    {
        $this->requiredResultSubstitutions[$actionId] = $substitutions;
    }

    #[Override]
    public function addHandlerSubstitution(ActionHandlerSubstitution $actionHandlerSubstitution): void
    {
        $this->handlerSubstitutions[$actionHandlerSubstitution->actionId] = $actionHandlerSubstitution;
    }

    #[Override]
    public function isSubstituteHandler(string $actionId): bool
    {
        return array_key_exists($actionId, $this->handlerSubstitutions);
    }

    #[Override]
    public function getSubstituteHandler(string $actionId): ActionHandlerSubstitution
    {
        return $this->handlerSubstitutions[$actionId];
    }

    #[Override]
    public function isSubstituteResult(string $actionId): bool
    {
        return array_key_exists($actionId, $this->requiredResultSubstitutions);
    }

    #[Override]
    public function getSubstituteResult(string $actionId): array
    {
        return $this->requiredResultSubstitutions[$actionId];
    }

    public function reset(): void
    {
        $this->handlerSubstitutions = [];
        $this->requiredResultSubstitutions = [];
    }
}
