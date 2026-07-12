<?php

declare(strict_types=1);

namespace PhpRussianRequisites\Interfaces;

interface ValueObject
{
    public static function createFromString(string $value): static;

    public function __toString(): string;
    public function getValue(): int|float|string;
}
