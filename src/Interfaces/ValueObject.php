<?php

declare(strict_types=1);

namespace PhpRussianRequisites\Interfaces;

use Stringable;

interface ValueObject extends Stringable
{
    public static function createFromString(string $value): static;

    public function getValue(): int|float|string;
}
