<?php

declare(strict_types=1);

namespace Warp\DevTool;

use Warp\Container\Factory\DefinitionTag;
use Warp\Container\ServiceProvider\AbstractServiceProvider;
use Warp\DevTool\ChangeLog\ChangeLogCommand;
use Warp\DevTool\Monorepo\Composer\MonorepoComposerCommand;
use Warp\DevTool\Monorepo\MonorepoDependencyCommand;
use Warp\DevTool\Refactor\MoveClass\MoveClassCommand;
use Warp\DevTool\Version\VersionCommand;

final class CommandsProvider extends AbstractServiceProvider
{
    public function provides(): iterable
    {
        yield DefinitionTag::CONSOLE_COMMAND;
        yield MoveClassCommand::class;
        yield ChangeLogCommand::class;
        yield MonorepoComposerCommand::class;
        yield VersionCommand::class;
        yield MonorepoDependencyCommand::class;
    }

    public function register(): void
    {
        $this->getContainer()->define(MoveClassCommand::class)->addTag(DefinitionTag::CONSOLE_COMMAND);
        $this->getContainer()->define(ChangeLogCommand::class)->addTag(DefinitionTag::CONSOLE_COMMAND);
        $this->getContainer()->define(MonorepoComposerCommand::class)->addTag(DefinitionTag::CONSOLE_COMMAND);
        $this->getContainer()->define(VersionCommand::class)->addTag(DefinitionTag::CONSOLE_COMMAND);
        $this->getContainer()->define(MonorepoDependencyCommand::class)->addTag(DefinitionTag::CONSOLE_COMMAND);
    }
}
