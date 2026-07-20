<?php

declare(strict_types=1);

namespace PhpRussianRequisites\ValueObjects\Codes\Grn;

use Override;
use PhpRussianRequisites\Exceptions\TypeCastingException;

/**
 * Объект-значение для ГРН юридического лица
 *
 * ГРН - государственный регистрационный номер
 */
final readonly class CompanyGrn extends Grn
{
    protected const string ERROR_MESSAGE_INVALID_LENGTH   = '%s должен иметь длину 13 цифр.';
    private const string ERROR_MESSAGE_CASTING_TO_PRIMARY = 'ГРН "%s" не является основным.';

    #[Override]
    protected function isValidLength(): bool
    {
        return $this->isLengthForCompany();
    }

    #[Override]
    protected function isValidClassificationAttribute(): bool
    {
        return $this->isValidCompanyClassificationAttribute();
    }

    public function toPrimary(): Ogrn
    {
        if (! $this->isPrimary()) {
            throw new TypeCastingException(
                sprintf(self::ERROR_MESSAGE_CASTING_TO_PRIMARY, (string) $this)
            );
        }

        return Ogrn::createFromString((string) $this);
    }

    #[Override]
    public function isPrimary(): bool
    {
        $ca = $this->getClassificationAttribute();

        return '1' === $ca || '5' === $ca;
    }
}
