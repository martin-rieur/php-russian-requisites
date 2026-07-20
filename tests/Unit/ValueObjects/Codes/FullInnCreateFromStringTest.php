<?php

declare(strict_types=1);

namespace PhpRussianRequisites\Tests\Unit\ValueObjects\Codes;

use PhpRussianRequisites\Exceptions\BadValueException;
use PhpRussianRequisites\Exceptions\ViolationOfInternalDataConsistencyException;
use PhpRussianRequisites\ValueObjects\Codes\FullInn;
use PhpRussianRequisites\ValueObjects\Codes\Inn;
use PhpRussianRequisites\ValueObjects\Codes\Kpp;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FullInn::class)]
#[UsesClass(Inn::class)]
#[UsesClass(Kpp::class)]
final class FullInnCreateFromStringTest extends TestCase
{
    private const string VALID_COMPANY_INN = '9876543210';
    private const string VALID_PERSON_INN  = '987654321018';
    private const string VALID_KPP         = '35234V111';
    private const string VALID_SEPARATOR   = '-/';

    private const string VALID_COMPANY_FULL_INN_STRING =
        self::VALID_COMPANY_INN . self::VALID_SEPARATOR . self::VALID_KPP;

    private const string VALID_PERSON_FULL_INN_STRING = self::VALID_PERSON_INN;

    /**
     * Проверяет входную строку вида ""
     */
    public function testEmptyStringInsteadFullInn(): void
    {
        self::expectException(ViolationOfInternalDataConsistencyException::class);
        self::expectExceptionMessageIsOrContains('Должен быть указан ИНН');

        $empty_string = '';

        FullInn::createFromString($empty_string);
    }

    /**
     * Проверяет входную строку вида "   "
     */
    public function testStringOfSpacesInsteadFullInn(): void
    {
        self::expectException(ViolationOfInternalDataConsistencyException::class);
        self::expectExceptionMessageIsOrContains('Должен быть указан ИНН');

        $string_of_spaces = '   ';

        FullInn::createFromString($string_of_spaces);
    }

    /**
     * Проверяет входную строку вида "/"
     */
    public function testSplitStringWithOnlySeparator(): void
    {
        self::expectException(ViolationOfInternalDataConsistencyException::class);
        self::expectExceptionMessageIsOrContains('Должен быть указан ИНН');

        $separator = self::VALID_SEPARATOR;
        $string_with_only_separator = self::VALID_SEPARATOR;

        FullInn::createFromString($string_with_only_separator, $separator);
    }

    /**
     * Проверяет входную строку вида "  ИННКомпании  /  КПП  "
     */
    public function testCompanySplitValidFullInnStringWithSpaces(): void
    {
        $separator = self::VALID_SEPARATOR;

        $full_inn_string_with_spaces = '';
        $full_inn_string_with_spaces .= '  ' . self::VALID_COMPANY_INN;
        $full_inn_string_with_spaces .= '  ' . $separator . '  ';
        $full_inn_string_with_spaces .= self::VALID_KPP . '  ';

        $full_inn = FullInn::createFromString($full_inn_string_with_spaces, $separator);

        self::assertNotEmpty($full_inn);
    }

    /**
     * Проверяет входную строку вида "  ИННЧеловека  "
     */
    public function testPersonSplitValidFullInnStringWithSpaces(): void
    {
        $full_inn_string_with_spaces = '  ' . self::VALID_PERSON_INN . '  ';

        $full_inn = FullInn::createFromString($full_inn_string_with_spaces);

        self::assertNotEmpty($full_inn);
    }

    /**
     * Проверяет входную строку вида "ИННКомпании/"
     */
    public function testCompanySplitFullInnStringWithoutKpp(): void
    {
        self::expectException(ViolationOfInternalDataConsistencyException::class);
        self::expectExceptionMessageIsOrContains('У юридических лиц должен быть указан КПП');

        $separator       = self::VALID_SEPARATOR;
        $full_inn_string = self::VALID_COMPANY_INN . $separator;

        FullInn::createFromString($full_inn_string, $separator);
    }

    /**
     * Проверяет входную строку вида "ИННКомпании/  "
     */
    public function testCompanySplitFullInnStringWithSpacesInsteadKpp(): void
    {
        self::expectException(ViolationOfInternalDataConsistencyException::class);
        self::expectExceptionMessageIsOrContains('У юридических лиц должен быть указан КПП');

        $separator       = self::VALID_SEPARATOR;
        $full_inn_string = self::VALID_COMPANY_INN . $separator . '  ';

        FullInn::createFromString($full_inn_string, $separator);
    }

    /**
     * Проверяет входную строку вида "  /КПП"
     */
    public function testCompanySplitFullInnStringWithSpacesInsteadInn(): void
    {
        self::expectException(ViolationOfInternalDataConsistencyException::class);
        self::expectExceptionMessageIsOrContains('Должен быть указан ИНН');

        $separator       = self::VALID_SEPARATOR;
        $full_inn_string = '  ' . $separator . self::VALID_KPP;

        FullInn::createFromString($full_inn_string, $separator);
    }

    /**
     * Проверяет входную строку вида "ИННЧеловека/"
     */
    public function testPersonSplitFullInnStringWithSeparatorAtEnd(): void
    {
        $separator       = self::VALID_SEPARATOR;
        $full_inn_string = self::VALID_PERSON_INN . $separator;

        $full_inn = FullInn::createFromString($full_inn_string, $separator);

        self::assertNotEmpty($full_inn);
    }

    public function testCompanySplitWithInvalidLetterSeparator(): void
    {
        self::expectException(BadValueException::class);
        self::expectExceptionMessageIsOrContains(
            'Разделитель ИНН и КПП не должен содержать цифры и заглавные латинские буквы'
        );

        $full_inn_string   = self::VALID_COMPANY_FULL_INN_STRING;
        $invalid_separator = 'ABC';

        FullInn::createFromString($full_inn_string, $invalid_separator);
    }

    public function testCompanySplitWithInvalidNumericSeparator(): void
    {
        self::expectException(BadValueException::class);
        self::expectExceptionMessageIsOrContains(
            'Разделитель ИНН и КПП не должен содержать цифры и заглавные латинские буквы'
        );

        $full_inn_string   = self::VALID_COMPANY_FULL_INN_STRING;
        $invalid_separator = '123';

        FullInn::createFromString($full_inn_string, $invalid_separator);
    }

    public function testCompanySplitWithInvalidEmptyStringSeparator(): void
    {
        self::expectException(BadValueException::class);
        self::expectExceptionMessageIsOrContains(
            'Разделитель ИНН и КПП не должен быть пустой строкой'
        );

        $full_inn_string   = self::VALID_COMPANY_FULL_INN_STRING;
        $invalid_separator = '';

        FullInn::createFromString($full_inn_string, $invalid_separator);
    }

    public function testPersonSplitWithInvalidLetterSeparator(): void
    {
        self::expectException(BadValueException::class);
        self::expectExceptionMessageIsOrContains(
            'Разделитель ИНН и КПП не должен содержать цифры и заглавные латинские буквы'
        );

        $full_inn_string   = self::VALID_PERSON_FULL_INN_STRING;
        $invalid_separator = 'ABC';

        FullInn::createFromString($full_inn_string, $invalid_separator);
    }

    public function testPersonSplitWithInvalidNumericSeparator(): void
    {
        self::expectException(BadValueException::class);
        self::expectExceptionMessageIsOrContains(
            'Разделитель ИНН и КПП не должен содержать цифры и заглавные латинские буквы'
        );

        $full_inn_string   = self::VALID_PERSON_FULL_INN_STRING;
        $invalid_separator = '123';

        FullInn::createFromString($full_inn_string, $invalid_separator);
    }

    public function testPersonSplitWithInvalidEmptyStringSeparator(): void
    {
        self::expectException(BadValueException::class);
        self::expectExceptionMessageIsOrContains(
            'Разделитель ИНН и КПП не должен быть пустой строкой'
        );

        $full_inn_string   = self::VALID_PERSON_FULL_INN_STRING;
        $invalid_separator = '';

        FullInn::createFromString($full_inn_string, $invalid_separator);
    }

    public function testCompanySplitFullInnWithValidSeparator(): void
    {
        $full_inn_string = self::VALID_COMPANY_FULL_INN_STRING;
        $separator       = self::VALID_SEPARATOR;

        $full_inn = FullInn::createFromString($full_inn_string, $separator);

        self::assertNotEmpty($full_inn);
    }

    public function testPersonSplitFullInnWithValidSeparator(): void
    {
        $full_inn_string = self::VALID_PERSON_FULL_INN_STRING;
        $separator       = self::VALID_SEPARATOR;

        $full_inn = FullInn::createFromString($full_inn_string, $separator);

        self::assertNotEmpty($full_inn);
    }
}
