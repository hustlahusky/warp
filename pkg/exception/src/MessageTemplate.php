<?php

declare(strict_types=1);

namespace Warp\Exception;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Contracts\Translation\TranslatorTrait;

final class MessageTemplate implements \Stringable, TranslatableInterface
{
    private static ?TranslatorInterface $defaultTranslator = null;

    /**
     * @param array<string,scalar|\Stringable> $parameters
     */
    private function __construct(
        private readonly string $template,
        private readonly array $parameters = [],
        private ?string $domain = null,
    ) {
    }

    public function __toString()
    {
        return $this->trans(self::getDefaultTranslator());
    }

    /**
     * @param array<string,scalar|\Stringable> $parameters
     */
    public static function new(string $template, array $parameters = [], ?string $domain = null): self
    {
        return new self($template, $parameters, $domain);
    }

    /**
     * @param self|scalar|\Stringable $template
     */
    public static function wrap(mixed $template): self
    {
        if ($template instanceof self) {
            return $template;
        }

        return new self((string)$template);
    }

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans($this->template, $this->parameters, $this->domain, $locale);
    }

    public function setDomain(?string $domain): void
    {
        $this->domain = $domain;
    }

    private static function getDefaultTranslator(): TranslatorInterface
    {
        return self::$defaultTranslator ??= new class() implements TranslatorInterface {
            use TranslatorTrait;
        };
    }
}
