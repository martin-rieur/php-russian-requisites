<?php

declare(strict_types=1);

namespace PhpRussianRequisites\ValueObjects\Codes\Grn;

use Override;
use PhpRussianRequisites\Exceptions\BadValueException;
use PhpRussianRequisites\Interfaces\ValueObject;

/**
 * Объект-значение для любого ГРН (государственный регистрационный номер)
 *
 * Подробности о структуре ОГРН, ОГРНИП и прочих ГРН смотри в
 * {@see http://publication.pravo.gov.ru/Document/View/0001201801170015
 * Приказе Министерства финансов Российской Федерации от 30.10.2017 № 165н}
 */
readonly class Grn implements ValueObject
{
    private const string ERROR_MESSAGE_EMPTY_STRING        = '%s не может быть пустой строкой.';
    private const string ERROR_MESSAGE_INVALID_SYMBOL      = '%s должен содержать только цифры.';
    protected const string ERROR_MESSAGE_INVALID_LENGTH    = '%s должен иметь длину 13 или 15 цифр.';
    private const string ERROR_MESSAGE_INVALID_CHECKSUM    = 'Неверная контрольная сумма %s (вероятно, допущена опечатка).';
    private const string ERROR_MESSAGE_INVALID_REGION_CODE = 'В %s неверно указан код региона РФ (четвёртая и пятая цифры не могут быть нулями одновременно).';
    private const string ERROR_MESSAGE_INVALID_CLASSIFICATION_ATTRIBUTE = 'Неверно указан признак отнесения %s записи (первая цифра)';

    private const int LENGTH_FOR_COMPANY      = 13;
    private const int LENGTH_FOR_ENTREPRENEUR = 15;

    private string $grn;

    #[Override]
    public static function createFromString(string $grn): static
    {
        return new static($grn);
    }

    final private function __construct(string $grn)
    {
        $this->grn = trim($grn);

        if ($this->isEmptyString()) {
            throw new BadValueException(
                sprintf(self::ERROR_MESSAGE_EMPTY_STRING, $this->name())
            );
        }

        if (! $this->isValidSymbols()) {
            throw new BadValueException(
                sprintf(self::ERROR_MESSAGE_INVALID_SYMBOL, $this->name())
            );
        }

        if (! $this->isValidLength()) {
            throw new BadValueException(
                sprintf(static::ERROR_MESSAGE_INVALID_LENGTH, $this->name())
            );
        }

        if (! $this->isValidChecksum()) {
            throw new BadValueException(
                sprintf(self::ERROR_MESSAGE_INVALID_CHECKSUM, $this->name())
            );
        }

        if (! $this->isValidRegionCode()) {
            throw new BadValueException(
                sprintf(self::ERROR_MESSAGE_INVALID_REGION_CODE, $this->name())
            );
        }

        if (! $this->isValidClassificationAttribute()) {
            throw new BadValueException(
                sprintf(self::ERROR_MESSAGE_INVALID_CLASSIFICATION_ATTRIBUTE, $this->name())
            );
        }
    }

    protected function name(): string
    {
        return 'ГРН';
    }

    private function isEmptyString(): bool
    {
        return '' === $this->grn;
    }

    private function isValidSymbols(): bool
    {
        return ctype_digit($this->grn);
    }

    protected function isValidLength(): bool
    {
        return $this->isLengthForCompany() || $this->isLengthForEntrepreneur();
    }

    public function isCompany(): bool
    {
        return $this->isLengthForCompany();
    }

    public function isEntrepreneur(): bool
    {
        return $this->isLengthForEntrepreneur();
    }

    public function isPrimary(): bool
    {
        $ca = $this->getClassificationAttribute();

        return '1' === $ca
            || '3' === $ca
            || '5' === $ca;
    }

    final protected function isLengthForCompany(): bool
    {
        return self::LENGTH_FOR_COMPANY === $this->length();
    }

    final protected function isLengthForEntrepreneur(): bool
    {
        return self::LENGTH_FOR_ENTREPRENEUR === $this->length();
    }

    private function length(): int
    {
        return strlen($this->grn);
    }

    /**
     * Валидация ГРН – в ГРН 13 цифра является контрольным разрядом.
     * 1) Выбрать 12-значное число ГРН (с 1-й по 12-ю цифру).
     * 2) Вычислить остаток от деления выбранного числа на 11.
     * 3) Сравнить младший разряд полученного остатка от деления с 13-й цифрой ГРН.
     *    Если они равны, то ГРН верный.
     *
     * Валидация ГРНИП – в ГРНИП 15 цифра является контрольным разрядом.
     * 1) Выбрать 14-значное число ГРНИП (с 1-й по 14-ю цифру).
     * 2) Вычислить остаток от деления выбранного числа на 13.
     * 3) Сравнить младший разряд полученного остатка от деления с 15-й цифрой ГРНИП.
     *    Если они равны, то ГРНИП верный.
     */
    private function isValidChecksum(): bool
    {
        $number        = (int) substr($this->grn, 0, -1);
        $control_digit = (int) substr($this->grn, -1, 1);
        $modulo        = $this->length() - 2;

        $remainder = ($number % $modulo) % 10;

        return $control_digit === $remainder;
    }

    protected function isValidClassificationAttribute(): bool
    {
        return true;
    }

    private function isValidRegionCode(): bool
    {
        return '00' !== $this->getRegionCode();
    }

    /**
     * Признак отнесения государственного регистрационного номера записи
     * (1-я цифра ГРН)
     */
    public function getClassificationAttribute(): string
    {
        return substr($this->grn, 0, 1);
    }

    /**
     * Год
     * (2-я и 3-я цифры ГРН)
     */
    public function getYear(): string
    {
        return substr($this->grn, 1, 2);
    }

    /**
     * Код региона РФ
     * (4-я и 5-я цифры ГРН)
     *
     * Диапазон допустимых значений: ["01"; "99"]
     */
    public function getRegionCode(): string
    {
        return substr($this->grn, 3, 2);
    }

    public function __toString(): string
    {
        return $this->getValue();
    }

    #[Override]
    public function getValue(): string
    {
        return $this->grn;
    }
}
