<?php

declare(strict_types=1);

namespace PhpRussianRequisites\ValueObjects\Codes;

use Override;
use PhpRussianRequisites\Exceptions\BadValueException;
use PhpRussianRequisites\Interfaces\ValueObject;

/**
 * Объект-значение для КПП (код причины постановки на учёт)
 *
 * Формат, согласно
 * {@see https://www.nalog.gov.ru/rn77/about_fts/docs/3970311/ Приказу ФНС России от 29.06.2012 № ММВ-7-6/435@}
 *
 * Формат: NNNNPPXXX
 *      NNNN - код инспекции, первые две цифры - код региона РФ, вторые - номер ИФНС
 *      РР - причина постановки на учет (учета сведений).
 *           Символ Р представляет собой цифру или заглавную букву латинского алфавита от А до Z.
 *           Числовое значение символов РР может принимать значение:
 *              для российской организации от 01 до 50 (01 - по месту ее нахождения);
 *              для иностранной организации от 51 до 99;
 *      XXX - порядковый номер постановки на учет (учета сведений) в налоговом органе по соответствующему основанию.
 *
 *
 * Примечания по валидации:
 *
 * Код региона НЕ МОЖЕТ быть 00. Такой код считается невалидным.
 * Существуют и другие коды, которые на данный момент
 * не присвоены ни одному субъекту РФ, но они считаются валидными.
 *
 * Причина постановки на учёт (5-я и 6-я цифры) НЕ МОЖЕТ быть равна 00.
 * Все остальные сочетания букв и цифр считаются валидными,
 * хотя и не все они добавлены в СППУНО в настоящий момент.
 */
final readonly class Kpp implements ValueObject
{
    private const ERROR_MESSAGE_EMPTY_STRING        = 'КПП не может быть пустой строкой.';
    private const ERROR_MESSAGE_INVALID_SYMBOL      = 'КПП должен должен содержать только цифры и заглавные латинские буквы.';
    private const ERROR_MESSAGE_INVALID_LENGTH      = 'КПП должен состоять из 9 символов.';
    private const ERROR_MESSAGE_INVALID_REGION_CODE = 'Неверно указан код региона РФ (первые две цифры не могут быть 00).';

    private const ERROR_MESSAGE_INVALID_REASON_FOR_REGISTRATION
        = ' Неверно указана причина постановки на учёт (пятая и шестая цифры не могут быть нулями одновременно).';
    private const ERROR_MESSAGE_INVALID_FORMAT
        = 'Неверный формат КПП:'
           . ' пятый и шестой символы должны быть цифрами или заглавными латинскими буквам,'
           . ' остальные символы должны быть цифрами.';

    private string $kpp;

    /**
     * @see self::__construct()
     */
    #[Override]
    public static function createFromString(string $kpp): static
    {
        return new self($kpp);
    }

    /**
     * @param string $kpp Пробелы по краям обрежутся
     * @throws BadValueException Если передана строка, не соответствующая формату КПП
     */
    private function __construct(string $kpp)
    {
        $this->kpp = trim($kpp);

        if ($this->isEmptyString()) {
            throw new BadValueException(
                self::ERROR_MESSAGE_EMPTY_STRING
            );
        }

        if (! $this->isValidFormat()) {
            // проверки допустимых символов и длины нужны для того,
            // чтобы конкретизировать ошибку, когда это возможно
            if (! $this->isValidSymbols()) {
                throw new BadValueException(
                    self::ERROR_MESSAGE_INVALID_SYMBOL
                );
            }

            if (! $this->isValidLength()) {
                throw new BadValueException(
                    self::ERROR_MESSAGE_INVALID_LENGTH
                );
            }

            throw new BadValueException(
                self::ERROR_MESSAGE_INVALID_FORMAT
            );
        }

        if (! $this->isValidRegionCode()) {
            throw new BadValueException(
                self::ERROR_MESSAGE_INVALID_REGION_CODE
            );
        }

        if (! $this->isValidReasonForRegistration()) {
            throw new BadValueException(
                self::ERROR_MESSAGE_INVALID_REASON_FOR_REGISTRATION
            );
        }
    }

    private function isEmptyString(): bool
    {
        return '' === $this->kpp;
    }

    private function isValidFormat(): bool
    {
        return 1 === preg_match('/^\d{4}[0-9A-Z]{2}\d{3}$/u', $this->kpp);
    }

    private function isValidSymbols(): bool
    {
        return 1 === preg_match('/^[0-9A-Z]*$/u', $this->kpp);
    }

    private function isValidLength(): bool
    {
        // т.к. в КПП используются только цифры и заглавные латинские буквы,
        // каждый символ весит ровно один байт,
        // поэтому можно использовать strlen()
        return 9 === strlen($this->kpp);
    }

    private function isValidReasonForRegistration(): bool
    {
        return '00' !== $this->getReasonForRegistration();
    }

    private function isValidRegionCode(): bool
    {
        return '00' !== $this->getRegionCode();
    }

    /**
     * Код региона РФ (1-я и 2-я цифры КПП)
     *
     * Диапазон допустимых значений: ["01"; "99"]
     */
    public function getRegionCode(): string
    {
        return substr($this->kpp, 0, 2);
    }

    /**
     * Причина постановки на учёт (5-я и 6-я цифры КПП)
     *
     * Полный список причин смотри в справочнике причин постановки на учет
     * налогоплательщиков-организаций в налоговых органах (СППУНО)
     */
    public function getReasonForRegistration(): string
    {
        return substr($this->kpp, 4, 2);
    }

    public function __toString(): string
    {
        return $this->getValue();
    }

    #[Override]
    public function getValue(): string
    {
        return $this->kpp;
    }
}
