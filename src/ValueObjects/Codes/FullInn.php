<?php

declare(strict_types=1);

namespace PhpRussianRequisites\ValueObjects\Codes;

use Override;
use PhpRussianRequisites\Exceptions\BadValueException;
use PhpRussianRequisites\Exceptions\ViolationOfInternalDataConsistencyException;
use PhpRussianRequisites\Interfaces\ValueObject;

/**
 * Объект-значение для хранения пары ИНН и КПП
 */
final readonly class FullInn implements ValueObject
{
    private const ERROR_MESSAGE_MISSING_INN     = 'Должен быть указан ИНН.';
    private const ERROR_MESSAGE_MISSING_KPP     = 'У юридических лиц должен быть указан КПП.';
    private const ERROR_MESSAGE_UNNECESSARY_KPP = 'У физических лиц не должен быть указан КПП.';

    private const ERROR_MESSAGE_SEPARATOR_IS_EMPTY_STRING   = 'Разделитель ИНН и КПП не должен быть пустой строкой.';
    private const ERROR_MESSAGE_INVALID_SYMBOL_IN_SEPARATOR = 'Разделитель ИНН и КПП не должен содержать цифры и заглавные латинские буквы.';

    private const REGEX_INVALID_SYMBOLS_OF_SEPARATOR = '/[0-9A-Z]/u';

    public const string DEFAULT_SEPARATOR = '/';

    /**
     * Создаёт объект FullInn из строки, содержащей ИНН и КПП, разделённые символами $separator;
     *
     * @throws ViolationOfInternalDataConsistencyException Если НЕ указано КПП у юридического лица
     * @throws ViolationOfInternalDataConsistencyException Если указано КПП у физического лица
     * @see self::validateSeparator()
     * @see Inn::__construct()
     * @see Kpp::__construct()
     */
    #[Override]
    public static function createFromString(
        string $full_inn_string,
        string $separator = self::DEFAULT_SEPARATOR,
    ): static {
        $segments = self::splitFullInnString($full_inn_string, $separator);
        $inn_string = $segments['inn'];
        $kpp_string = $segments['kpp'];

        return self::createFromStrings($inn_string, $kpp_string);
    }

    /**
     * @return array{inn:string,kpp:string}
     */
    private static function splitFullInnString(
        string $full_inn_string,
        string $separator,
    ): array {
        self::validateSeparator($separator);

        $segments = explode($separator, $full_inn_string);

        return [
            'inn' => $segments[0],
            'kpp' => $segments[1] ?? '',
        ];
    }

    /**
     * Для физических лиц в $kpp_string не нужно ничего передавать,
     * но можно передать пустую строку
     *
     * @see self::__construct()
     * @see Inn::__construct()
     * @see Kpp::__construct()
     */
    public static function createFromStrings(
        string $inn_string,
        string $kpp_string = '',
    ): self {
        $inn_string = trim($inn_string);
        $kpp_string = trim($kpp_string);

        if ('' === $inn_string) {
            throw new ViolationOfInternalDataConsistencyException(
                self::ERROR_MESSAGE_MISSING_INN
            );
        }

        $inn = Inn::createFromString($inn_string);
        $kpp = ('' !== $kpp_string)
            ? Kpp::createFromString($kpp_string)
            : null;

        return new self($inn, $kpp);
    }

    /**
     * @throws ViolationOfInternalDataConsistencyException Если НЕ указано КПП у юридического лица
     * @throws ViolationOfInternalDataConsistencyException Если указано КПП у физического лица
     */
    private function __construct(
        private Inn $inn,
        private ?Kpp $kpp = null
    ) {
        if ($this->missingRequiredKpp()) {
            throw new ViolationOfInternalDataConsistencyException(
                self::ERROR_MESSAGE_MISSING_KPP,
            );
        }

        if ($this->hasUnnecessaryKpp()) {
            throw new ViolationOfInternalDataConsistencyException(
                self::ERROR_MESSAGE_UNNECESSARY_KPP
            );
        }
    }

    private function missingRequiredKpp(): bool
    {
        return $this->isRequiredKpp() && ! $this->hasKpp();
    }

    private function hasUnnecessaryKpp(): bool
    {
        return ! $this->isRequiredKpp() && $this->hasKpp();
    }

    private function isRequiredKpp(): bool
    {
        return $this->isCompany();
    }

    /**
     * Возвращает true, если ИНН принадлежит юридическому лицу
     *
     * @return boolean
     */
    public function isCompany(): bool
    {
        return $this->inn->isCompany();
    }

    /**
     * Возвращает true, если ИНН принадлежит физическому лицу
     *
     * @return boolean
     */
    public function isPerson(): bool
    {
        return $this->inn->isPerson();
    }

    private function hasKpp(): bool
    {
        return null !== $this->kpp;
    }

    #[Override]
    public function __toString(): string
    {
        return $this->getValue();
    }

    /**
     * Для юридических лиц возвращает строку, содержащую ИНН и КПП, разделённые символами $separator;
     * для физических лиц возвращает строку, содержащую только ИНН
     *
     * @see self::validateSeparator()
     */
    #[Override]
    public function getValue(string $separator = self::DEFAULT_SEPARATOR): string
    {
        self::validateSeparator($separator);

        if ($this->isPerson()) {
            return $this->getInnString();
        }

        return $this->getInnString() . $separator . $this->getKppString();
    }

    /**
     * Проверяет допустимость разделителя ИНН и КПП;
     * для недопустимого разделителя выбрасывает исключение
     *
     * @throws BadValueException Если разделитель является пустой строкой
     * @throws BadValueException Если разделитель содержит цифры или заглавные латинские буквы
     * @phpstan-assert non-empty-string $separator
     * @phpstan-assert !numeric-string $separator
     */
    private static function validateSeparator(string $separator): void
    {
        if ('' === $separator) {
            throw new BadValueException(
                self::ERROR_MESSAGE_SEPARATOR_IS_EMPTY_STRING
            );
        }

        if (1 === preg_match(self::REGEX_INVALID_SYMBOLS_OF_SEPARATOR, $separator)) {
            throw new BadValueException(
                self::ERROR_MESSAGE_INVALID_SYMBOL_IN_SEPARATOR
            );
        }
    }

    public function getInnString(): string
    {
        return (string) $this->inn;
    }

    /**
     * Для физических лиц возвращает пустую строку
     */
    public function getKppString(): string
    {
        return (string) $this->kpp;
    }
}
