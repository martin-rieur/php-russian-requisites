<?php

declare(strict_types=1);

namespace PhpRussianRequisites\ValueObjects\Codes\Grn;

use Override;
use PhpRussianRequisites\Exceptions\TypeCastingException;

/**
 * Объект-значение для ГРН индивидуального предпринимателя
 *
 * ГРН - государственный регистрационный номер
 */
final readonly class EntrepreneurGrn extends Grn
{
    protected const string ERROR_MESSAGE_INVALID_LENGTH   = '%s должен иметь длину 15 цифр.';
    private const string ERROR_MESSAGE_CASTING_TO_PRIMARY = 'ГРН "%s" не является основным.';

    #[Override]
    protected function isValidLength(): bool
    {
        return $this->isLengthForEntrepreneur();
    }

    #[Override]
    protected function isValidClassificationAttribute(): bool
    {
        $ca = $this->getClassificationAttribute();

        return '3' === $ca || '4' === $ca;
    }

    public function toPrimary(): Ogrnip
    {
        if (! $this->isPrimary()) {
            throw new TypeCastingException(
                sprintf(self::ERROR_MESSAGE_CASTING_TO_PRIMARY, (string) $this)
            );
        }

        return Ogrnip::createFromString((string) $this);
    }

    #[Override]
    public function isPrimary(): bool
    {
        $ca = $this->getClassificationAttribute();

        return '3' === $ca;
    }
}
