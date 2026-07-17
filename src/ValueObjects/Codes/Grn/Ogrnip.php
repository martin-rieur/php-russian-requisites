<?php

declare(strict_types=1);

namespace PhpRussianRequisites\ValueObjects\Codes\Grn;

use Override;

/**
 * Объект-значение для ОГРНИП (основной государственный регистрационный номер индивидуального предпринимателя)
 */
final readonly class Ogrnip extends Grn
{
    protected const string ERROR_MESSAGE_INVALID_LENGTH = '%s должен иметь длину 15 цифр.';

    #[Override]
    protected function name(): string
    {
        return 'ОГРНИП';
    }

    #[Override]
    protected function isValidLength(): bool
    {
        return $this->isLengthForEntrepreneur();
    }

    #[Override]
    protected function isValidClassificationAttribute(): bool
    {
        $ca = $this->getClassificationAttribute();

        return '3' === $ca;
    }
}
