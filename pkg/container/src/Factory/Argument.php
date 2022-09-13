<?php

declare(strict_types=1);

namespace Warp\Container\Factory;

use PhpOption\None;
use PhpOption\Option;
use PhpOption\Some;
use Psr\Container\ContainerInterface;
use Warp\Container\ContainerAwareInterface;
use Warp\Container\DefinitionAggregateInterface;
use Warp\Container\Exception\CannotInstantiateAbstractClassException;
use Warp\Container\Exception\CannotResolveArgumentException;
use Warp\Container\Exception\ContainerException;
use Warp\Container\FactoryOptionsInterface;
use Warp\Container\InstanceOfAliasContainer;
use Warp\Type\AbstractAggregatedType;
use Warp\Type\InstanceOfType;
use Warp\Type\MixedType;
use Warp\Type\TypeInterface;

/**
 * @template A
 */
final class Argument implements ContainerAwareInterface
{
    private readonly TypeInterface $type;

    /**
     * @var Option<A>
     */
    private readonly Option $defaultValue;
    private readonly bool $variadic;
    private ?InstanceOfAliasContainer $container = null;

    /**
     * @param Option<A>|null $defaultValue
     */
    public function __construct(
        private readonly string $name,
        private readonly string $location,
        ?TypeInterface $type = null,
        ?Option $defaultValue = null,
        bool $variadic = false,
    ) {
        $type ??= MixedType::new();

        if ($variadic) {
            $defaultValue ??= new Some([]);
        }

        if ($type->check(null)) {
            $defaultValue ??= new Some(null);
        }
        $this->type = $type;
        /** @phpstan-var Option<A>|null $defaultValue */
        $this->defaultValue = $defaultValue ?? None::create();
        $this->variadic = $variadic;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    /**
     * @return \Generator<A>
     */
    public function resolve(?FactoryOptionsInterface $options = null): \Generator
    {
        /** @var Option<A> $value */
        $value = Option::ensure(fn () => $this->resolveUsingOptions($options))
            ->orElse(Option::ensure(fn () => $this->resolveUsingType($this->type)))
            ->orElse(Option::ensure(fn () => $this->defaultValue));

        if (!$value->isDefined()) {
            throw new CannotResolveArgumentException(
                $this,
                null,
                'Argument has not default value and cannot be resolved through container nor given options.'
            );
        }

        $value = $value->get();

        if ($this->variadic && \is_iterable($value)) {
            return yield from $value;
        }

        return yield $value;
    }

    public function setContainer(?ContainerInterface $container): void
    {
        $this->container = null === $container ? null : InstanceOfAliasContainer::wrap($container);
    }

    public function getContainer(): InstanceOfAliasContainer
    {
        if (null === $this->container) {
            throw new ContainerException('No container implementation has been set.');
        }

        return $this->container;
    }

    /**
     * @return Option<A>
     */
    private function resolveUsingOptions(?FactoryOptionsInterface $options = null): Option
    {
        if (null === $options) {
            return None::create();
        }

        if ($options->hasArgument($this->name)) {
            return $options->getArgument($this->name);
        }

        if ($this->variadic && null !== $tag = $options->getArgumentTag($this->name)) {
            return $this->resolveFromContainerByTag($tag);
        }

        if (null !== $alias = $options->getArgumentAlias($this->name)) {
            return $this->resolveFromContainer($alias);
        }

        return None::create();
    }

    /**
     * @return Option<A>
     */
    private function resolveUsingType(TypeInterface $type): Option
    {
        if ($type instanceof AbstractAggregatedType) {
            foreach ($type as $subtype) {
                $output = $this->resolveUsingType($subtype);

                if ($output->isEmpty()) {
                    continue;
                }

                return $output;
            }
        }

        if ($type instanceof InstanceOfType) {
            $key = (string)$type;

            if ($this->variadic) {
                $output = $this->resolveFromContainerByTag($key);

                if ($output->isDefined()) {
                    return $output;
                }
            }

            return $this->resolveFromContainer($key);
        }

        return None::create();
    }

    /**
     * @param string|class-string<A> $alias
     * @return Option<A>
     */
    private function resolveFromContainer(string $alias): Option
    {
        try {
            $container = $this->getContainer();

            if (!$container->has($alias)) {
                return None::create();
            }

            return new Some($container->get($alias));
        } catch (CannotInstantiateAbstractClassException) {
            return None::create();
        } catch (\Throwable $e) {
            throw new CannotResolveArgumentException($this, $e);
        }
    }

    /**
     * @return Option<A>
     */
    private function resolveFromContainerByTag(string $tag): Option
    {
        try {
            $container = $this->getContainer();

            if (!$container->has(DefinitionAggregateInterface::class)) {
                return None::create();
            }

            /** @var DefinitionAggregateInterface $definitionContainer */
            $definitionContainer = $container->get(DefinitionAggregateInterface::class);

            if (!$definitionContainer->hasTagged($tag)) {
                return None::create();
            }

            // @phpstan-ignore-next-line
            return new Some(\iterator_to_array($definitionContainer->getTagged($tag), false));
        } catch (CannotInstantiateAbstractClassException) {
            return None::create();
        } catch (\Throwable $e) {
            throw new CannotResolveArgumentException($this, $e);
        }
    }
}
