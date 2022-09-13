<?php

declare(strict_types=1);

namespace Warp\Bridge\Cycle\Migrator\Handler;

use Cycle\Migrations\Migrator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\OutputStyle;
use Warp\Bridge\Cycle\Migrator\Command\MigratorListCommand;

final class MigratorListCommandHandler extends AbstractCommandHandler
{
    public function __construct(
        private readonly Migrator $migrator,
    ) {
    }

    public function handle(Command $command, InputInterface $input, OutputStyle $style): int
    {
        if (!$command instanceof MigratorListCommand) {
            return Command::INVALID;
        }

        $rows = \iterator_to_array($this->getTableRows($command->getDateFormat()), false);

        if (0 === \count($rows)) {
            $style->text('No migrations were found.');

            return Command::SUCCESS;
        }

        $style->table(['Name', 'Created At', 'Executed At'], $rows);

        return Command::SUCCESS;
    }

    /**
     * @return \Generator<TableSeparator|array{string,string,string}>
     */
    private function getTableRows(string $dateFormat): \Generator
    {
        $prevStatus = null;
        foreach ($this->migrator->getMigrations() as $migration) {
            $state = $migration->getState();

            $prevStatus ??= $state->getStatus();
            if ($prevStatus !== $state->getStatus()) {
                yield new TableSeparator();
            }
            $prevStatus = $state->getStatus();

            yield [
                $state->getName(),
                $state->getTimeCreated()->format($dateFormat),
                null === $state->getTimeExecuted()
                    ? '<fg=blue>not executed yet</>'
                    : \sprintf('<fg=green>%s</>', $state->getTimeExecuted()->format($dateFormat)),
            ];
        }
    }
}
