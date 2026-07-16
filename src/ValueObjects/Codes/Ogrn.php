<?php

declare(strict_types=1);

namespace PhpRussianRequisites\ValueObjects\Codes;

use Override;

/**
 * Объект-значение для ОГРН (основной государственный регистрационный номер)
 */
final readonly class Ogrn extends Grn
{
    protected const string ERROR_MESSAGE_INVALID_LENGTH = '%s должен иметь длину 13 цифр.';

    #[Override]
    protected function name(): string
    {
        return 'ОГРН';
    }

    #[Override]
    protected function isValidLength(): bool
    {
        return $this->isLengthForCompany();
    }

    #[Override]
    protected function isValidClassificationAttribute(): bool
    {
        $ca = $this->getClassificationAttribute();

        return '1' === $ca || '5' === $ca;
    }
}
