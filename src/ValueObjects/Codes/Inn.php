<?php

declare(strict_types=1);

namespace PhpRussianRequisites\ValueObjects\Codes;

use Override;
use PhpRussianRequisites\Exceptions\BadValueException;
use PhpRussianRequisites\Interfaces\ValueObject;

/**
 * Объект-значение для ИНН (идентификационный номер налогоплательщика)
 *
 * Формат, согласно
 * {@see https://www.nalog.gov.ru/rn77/about_fts/docs/3970311/ Приказу ФНС России от 29.06.2012 № ММВ-7-6/435@}
 *
 * У юридических лиц длина ИНН 10 цифр,
 * у физических лиц (в том числе у ИП и КФХ) длина ИНН 12 цифр
 *
 * Формат ИНН:
 *      для юридических лиц: NNNNXXXXXC
 *      для физических лиц:  NNNNXXXXXXCC
 *
 * NNNN - код инспекции, выдавшей ИНН; первые две цифры - код региона РФ
 * XXXXX - порядковый номер налогоплательщика
 * C - контрольная цифра (одна у юридического лица, две у физического лица)
 *
 *
 * Примечания по валидации:
 * Код региона НЕ МОЖЕТ быть 00. Такой код считается невалидным.
 * Существуют и другие коды, которые на данный момент
 * не присвоены ни одному субъекту РФ, но они считаются валидными.
 *
 * Третья и четвёртая цифры МОГУТ быть равны 00.
 * Таких ИНН много.
 * Как-либо дополнительно проверить эти цифры нельзя.
 *
 * Порядковый номер налогоплательщика МОЖЕТ быть 00000.
 * Таких ИНН сейчас всего 4: 7810000001, 5949000003, 3257000008, 0816000005.
 * В общем случае, эти цифры дополнительно проверить нельзя.
 */
final readonly class Inn implements ValueObject
{
    private const ERROR_MESSAGE_EMPTY_STRING             = 'ИНН не может быть пустой строкой.';
    private const ERROR_MESSAGE_INVALID_SYMBOL           = 'ИНН должен состоять только из цифр.';
    private const ERROR_MESSAGE_INVALID_LENGTH           = 'ИНН должен иметь длину 10 или 12 цифр.';
    private const ERROR_MESSAGE_INVALID_COMPANY_CHECKSUM = 'Неверная контрольная сумма ИНН юридического лица.';
    private const ERROR_MESSAGE_INVALID_PERSON_CHECKSUM  = 'Неверная контрольная сумма ИНН физического лица.';
    private const ERROR_MESSAGE_INVALID_REGION_CODE      = 'Неверно указан код региона РФ (первые две цифры не могут быть 00).';

    private const int LENGTH_FOR_COMPANY = 10;
    private const int LENGTH_FOR_PERSON  = 12;

    private string $inn;

    /**
     * @see self::__construct()
    */
    #[Override]
    public static function createFromString(string $inn): static
    {
        return new self($inn);
    }

    /**
     * @param string $inn Пробелы по краям обрежутся
     * @throws BadValueException Если передана строка, не соответствующая формату ИНН
     */
    public function __construct(string $inn)
    {
        $this->inn = trim($inn);

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

        switch (true) {
            case $this->isLengthForCompany():
                if (! $this->isValidCompanyChecksum()) {
                    throw new BadValueException(
                        self::ERROR_MESSAGE_INVALID_COMPANY_CHECKSUM
                    );
                }
                break;
            case $this->isLengthForPerson():
                if (! $this->isValidPersonChecksum()) {
                    throw new BadValueException(
                        self::ERROR_MESSAGE_INVALID_PERSON_CHECKSUM
                    );
                }
                break;
            default:
                throw new BadValueException(
                    self::ERROR_MESSAGE_INVALID_LENGTH
                );
        }

        if (! $this->isValidRegionCode()) {
            throw new BadValueException(
                self::ERROR_MESSAGE_INVALID_REGION_CODE
            );
        }
    }

    private function isEmptyString(): bool
    {
        return '' === $this->inn;
    }

    private function isValidSymbols(): bool
    {
        return ctype_digit($this->inn);
    }

    public function isCompany(): bool
    {
        return $this->isLengthForCompany();
    }

    public function isPerson(): bool
    {
        return $this->isLengthForPerson();
    }

    private function isLengthForCompany(): bool
    {
        return self::LENGTH_FOR_COMPANY === $this->length();
    }

    private function isLengthForPerson(): bool
    {
        return self::LENGTH_FOR_PERSON === $this->length();
    }

    /**
     * Валидация ИНН организации – в ИНН организации 10-я цифра является контрольным разрядом.
     * 1) Вычислить сумму произведений цифр ИНН (с 1-й по 9-ю) на следующие коэффициенты:
     *    2, 4, 10, 3, 5, 9, 4, 6, 8 (т.е. 2 * ИНН[1] + 4 * ИНН[2] + ...).
     * 2) Вычислить остаток от деления полученной суммы на 11.
     * 3) Сравнить младший разряд полученного остатка от деления с младшим разрядом ИНН.
     * 4) Если они равны, то ИНН верный.
     */
    private function isValidCompanyChecksum(): bool
    {
        $weights = [2, 4, 10, 3, 5, 9, 4, 6, 8];

        $sum = $this->calculateChecksum(...$weights);

        $remainder = ($sum % 11) % 10;

        return $this->getLastDigit() === $remainder;
    }

    /**
     * Валидация ИНН ИП и физлиц – в ИНН ИП и физлиц 11-я и 12-я цифры являются контрольным числом.
     * 1) Вычислить 1-ю контрольную цифру:
     *    Вычислить сумму произведений цифр ИНН (с 1-й по 10-ю) на следующие коэффициенты:
     *    7, 2, 4, 10, 3, 5, 9, 4, 6, 8 (т.е. 7 * ИНН[1] + 2 * ИНН[2] + ...)
     * 2) Вычислить младший разряд остатка от деления полученной суммы на 11.
     * 3) Вычислить 2-ю контрольную цифру:
     *    Вычислить сумму произведений цифр ИНН (с 1-й по 11-ю) на следующие коэффициенты:
     *    3, 7, 2, 4, 10, 3, 5, 9, 4, 6, 8 (т.е. 3 * ИНН[1] + 7 * ИНН[2] + ...)
     * 4) Вычислить младший разряд остатка от деления полученной суммы на 11.
     * 5) Сравнить 1-ю контрольную цифру с 11-й цифрой ИНН
     *    и сравнить 2-ю контрольную цифру с 12-й цифрой ИНН.
     *    Если они равны, то ИНН верный.
     */
    private function isValidPersonChecksum(): bool
    {
        $weights_10 = [7, 2, 4, 10, 3, 5, 9, 4, 6, 8];
        $weights_11 = [3, 7, 2, 4, 10, 3, 5, 9, 4, 6, 8];

        $sum_10 = $this->calculateChecksum(...$weights_10);
        $sum_11 = $this->calculateChecksum(...$weights_11);

        $remainder_10 = ($sum_10 % 11) % 10;
        $remainder_11 = ($sum_11 % 11) % 10;

        return (
            $this->getPenultimateDigit() === $remainder_10
            && $this->getLastDigit() === $remainder_11
        );
    }

    private function calculateChecksum(int ...$weights): int
    {
        $sum = 0;

        for ($position = 0; $position < count($weights); $position++) {
            $weight = $weights[$position];
            $digit  = $this->getDigit($position);

            $sum += $weight * $digit;
        }

        return $sum;
    }

    private function getPenultimateDigit(): int
    {
        return $this->getDigit($this->length() - 2);
    }

    private function getLastDigit(): int
    {
        return $this->getDigit($this->length() - 1);
    }

    private function getDigit(int $position): int
    {
        return (int) $this->inn[$position];
    }

    private function length(): int
    {
        return strlen($this->inn);
    }

    private function isValidRegionCode(): bool
    {
        return '00' !== $this->getRegionCode();
    }

    /**
     * Код региона РФ
     * (1-я и 2-я цифры ИНН)
     *
     * Диапазон допустимых значений: ["01"; "99"]
     */
    public function getRegionCode(): string
    {
        return substr($this->inn, 0, 2);
    }

    public function __toString(): string
    {
        return $this->getValue();
    }

    #[Override]
    public function getValue(): string
    {
        return $this->inn;
    }
}
