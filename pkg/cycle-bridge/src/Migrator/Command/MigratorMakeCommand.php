<?php

declare(strict_types=1);

namespace Warp\Bridge\Cycle\Migrator\Command;

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Warp\Bridge\Cycle\Migrator\Handler;
use Warp\Bridge\Cycle\Migrator\Input\InputArgument;
use Warp\Bridge\Cycle\Migrator\Input\InputOption;

final class MigratorMakeCommand extends Command
{
    protected static $defaultName = 'migrator:make';
    protected static $defaultDescription = 'Create a new migration file';

    /**
     * @var InputArgument<string>
     */
    private readonly InputArgument $name;

    /**
     * @var InputOption<string>
     */
    private readonly InputOption $database;

    public function __construct(
        private readonly ContainerInterface $container,
        ?string $name = null,
    ) {
        parent::__construct($name);

        $this->name = new InputArgument('name', InputArgument::REQUIRED, 'A migration filename');
        $this->name->register($this);

        $this->database = new InputOption('database', null, InputOption::VALUE_OPTIONAL, 'A database name', 'default');
        $this->database->register($this);
    }

    public function getMigrationName(InputInterface $input): string
    {
        return $this->name->getValueFrom($input);
    }

    public function getMigrationDatabase(InputInterface $input): string
    {
        return $this->database->getValueFrom($input);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        return $this->container->get(Handler\MigratorMakeCommandHandler::class)->handle($this, $input, $io);
    }
}
