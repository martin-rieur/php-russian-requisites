<?php

declare(strict_types=1);

namespace PhpRussianRequisites\ValueObjects\Codes;

use Override;
use PhpRussianRequisites\Interfaces\ValueObject;
use PhpRussianRequisites\Exceptions\BadValueException;
use PhpRussianRequisites\Exceptions\OutOfRangeOfStringException;

/**
 * Объект-значение для кода ОКПО (общероссийский классификатор предприятий и организаций)
 */
final readonly class Okpo implements ValueObject
{
    private const ERROR_MESSAGE_EMPTY_STRING        = 'Код ОКПО не может быть пустой строкой.';
    private const ERROR_MESSAGE_INVALID_SYMBOL      = 'Код ОКПО должен содержать только цифры.';
    private const ERROR_MESSAGE_INVALID_LENGTH      = 'Код ОКПО должен иметь длину 8 или 10 цифр.';
    private const ERROR_MESSAGE_INVALID_CHECKSUM    = 'Неверная контрольная сумма кода ОКПО.';
    private const ERROR_MESSAGE_CONTAINS_ONLY_ZEROS = 'Код ОКПО не должен состоять из одних нулей.';

    private const LENGTH_FOR_COMPANY       = 8;
    private const LENGTH_FOR_ENTREPRENEUR  = 10;

    private string $okpo;

    #[Override]
    public static function createFromString(string $okpo): static
    {
        return new self($okpo);
    }

    private function __construct(string $okpo)
    {
        $this->okpo = trim($okpo);

        if ($this->isEmptyString()) {
            throw new BadValueException(
                self::ERROR_MESSAGE_EMPTY_STRING
            );
        }

        if (! $this->isValidSymbols()) {
            throw new BadValueException(
                self::ERROR_MESSAGE_INVALID_SYMBOL
            );
        }

        if ($this->containsOnlyZeros()) {
            throw new BadValueException(
                self::ERROR_MESSAGE_CONTAINS_ONLY_ZEROS
            );
        }

        if (! $this->isValidLength()) {
            throw new BadValueException(
                self::ERROR_MESSAGE_INVALID_LENGTH
            );
        }

        if (! $this->isValidChecksum()) {
            throw new BadValueException(
                self::ERROR_MESSAGE_INVALID_CHECKSUM
            );
        }
    }

    public function isCompany(): bool
    {
        return $this->isLengthForCompany();
    }

    public function isEntrepreneur(): bool
    {
        return $this->isLengthForEntrepreneur();
    }

    private function isEmptyString(): bool
    {
        return '' === $this->okpo;
    }

    private function isValidSymbols(): bool
    {
        return ctype_digit($this->okpo);
    }

    private function isValidLength(): bool
    {
        return $this->isLengthForCompany() || $this->isLengthForEntrepreneur();
    }

    private function containsOnlyZeros(): bool
    {
        return 1 === preg_match('/^0+$/', $this->okpo);
    }

    private function isLengthForCompany(): bool
    {
        return self::LENGTH_FOR_COMPANY === $this->length();
    }

    private function isLengthForEntrepreneur(): bool
    {
        return self::LENGTH_FOR_ENTREPRENEUR === $this->length();
    }

    /**
     * Алгоритм расчёта, согласно "Пр 50.1.024-2005" и "ГОСТ р 1.20-2025"
     *
     * В коде ОКПО последняя цифра является контрольным числом.
     * Контрольное число рассчитывается следующим образом:
     * 1) Разрядам кода в общероссийском классификаторе, начиная со старшего разряда,
     *    присваивается набор весов, соответствующий натуральному ряду чисел от 1 до 10.
     *    Если разрядность кода больше 10, то набор весов повторяется;
     * 2) Каждая цифра кода умножается на вес разряда, и вычисляется сумма полученных произведений;
     * 3) Контрольное число для кода представляет собой остаток от деления полученной суммы на модуль "11";
     * 4) Контрольное число должно иметь один разряд, значение которого находится в пределах от 0 до 9.
     *
     *    Если получается остаток, равный 10, то для обеспечения одноразрядного контрольного числа
     *    необходимо провести повторный расчет, применяя вторую последовательность весов,
     *    сдвинутую на два разряда влево (3, 4, 5, ...);
     *
     *    Если в случае повторного расчета остаток от деления вновь сохраняется равным 10,
     *    то значение контрольного числа проставляется равным 0.
     */
    private function isValidChecksum(): bool
    {
        $weights = array_merge(range(1, 10), range(1, 10));

        $first_sum       = $this->calculateChecksum($weights);
        $first_remainder = $first_sum % 11;

        if ($first_remainder < 10) {
            return $this->getLastDigit() === $first_remainder;
        }

        $second_sum       = $this->calculateChecksum($weights, weight_offset: 2);
        $second_remainder = ($second_sum % 11) % 10;

        return $this->getLastDigit() === $second_remainder;
    }

    /**
     * @param int[] $weights
     */
    private function calculateChecksum(array $weights, int $weight_offset = 0): int
    {
        $sum = 0;

        for ($position = 0; $position < $this->length() - 1; $position++) {
            $weight =
                $weights[$position + $weight_offset]
                ?? throw new \OutOfRangeException(
                    sprintf(
                        'Непредвиденная ошибка при вычислении контрольной суммы кода ОКПО %s.',
                        $this->okpo,
                    ),
                );
            $digit  = $this->getDigit($position);

            $sum += $weight * $digit;
        }

        return $sum;
    }

    private function getLastDigit(): int
    {
        return $this->getDigit($this->length() - 1);
    }

    private function getDigit(int $position): int
    {
        $digit =
            $this->okpo[$position]
            ?? throw new OutOfRangeOfStringException(
                sprintf(
                    'Попытка получить из строки "%s", содержащей код ОКПО, символ на позиции %d',
                    $this->okpo,
                    $position,
                ),
            );

        return (int) $digit;
    }

    private function length(): int
    {
        return strlen($this->okpo);
    }

    #[Override]
    public function __toString(): string
    {
        return $this->getValue();
    }

    #[Override]
    public function getValue(): string
    {
        return $this->okpo;
    }
}
