<?php

declare(strict_types=1);

namespace Warp\Bridge\Cycle\Mapper\Plugin;

use Cycle\ORM\Command\Branch\ContextSequence;
use Cycle\ORM\Command\Branch\Sequence;
use Cycle\ORM\Command\CommandInterface;
use Cycle\ORM\Command\ContextCarrierInterface;
use Cycle\ORM\Heap\Node;
use Cycle\ORM\Heap\State;
use Symfony\Contracts\EventDispatcher\Event;

final class QueueAfterEvent extends Event
{
    private CommandInterface $command;

    public function __construct(
        private readonly object $entity,
        private readonly Node $node,
        private readonly State $state,
        private readonly CommandInterface $sourceCommand,
    ) {
        $this->command = $sourceCommand;
    }

    public function getEntity(): object
    {
        return $this->entity;
    }

    public function getNode(): Node
    {
        return $this->node;
    }

    public function getState(): State
    {
        return $this->state;
    }

    public function getCommand(): CommandInterface
    {
        return $this->command;
    }

    public function replaceCommand(CommandInterface $command): void
    {
        if ($this->sourceCommand instanceof ContextCarrierInterface && !$command instanceof ContextCarrierInterface) {
            throw new \InvalidArgumentException(\sprintf(
                'You cannot replace source command (%s) with given command (%s). It should implements ContextCarrierInterface',
                \get_debug_type($this->sourceCommand),
                \get_debug_type($command),
            ));
        }

        $this->command = $command;
    }

    /**
     * @return Sequence<CommandInterface>|ContextSequence<CommandInterface>
     */
    public function makeSequence(CommandInterface $command): Sequence|ContextSequence
    {
        if ($command instanceof ContextSequence || $command instanceof Sequence) {
            return $command;
        }

        if ($command instanceof ContextCarrierInterface) {
            $seq = new ContextSequence();
            $seq->addPrimary($command);
            return $seq;
        }

        $seq = new Sequence();
        $seq->addCommand($command);
        return $seq;
    }
}
