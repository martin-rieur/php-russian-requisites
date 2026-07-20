<?php

declare(strict_types=1);

namespace PhpRussianRequisites\Tests\Unit\ValueObjects\Codes;

use PhpRussianRequisites\Exceptions\BadValueException;
use PhpRussianRequisites\ValueObjects\Codes\FullInn;
use PhpRussianRequisites\ValueObjects\Codes\Inn;
use PhpRussianRequisites\ValueObjects\Codes\Kpp;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FullInn::class)]
#[UsesClass(Inn::class)]
#[UsesClass(Kpp::class)]
final class FullInnGetValueTest extends TestCase
{
    private const string VALID_COMPANY_INN = '9876543210';
    private const string VALID_PERSON_INN  = '987654321018';
    private const string VALID_KPP         = '35234V111';
    private const string VALID_SEPARATOR   = '/';

    public function testCompanyGetValueWithInvalidLetterSeparator(): void
    {
        self::expectException(BadValueException::class);
        self::expectExceptionMessageIsOrContains(
            'Разделитель ИНН и КПП не должен содержать цифры и заглавные латинские буквы'
        );

        $full_inn = $this->getCompanyFullInn();
        $invalid_separator = 'ABCD';

        $full_inn->getValue($invalid_separator);
    }

    public function testCompanyGetValueInvalidNumericSeparator(): void
    {
        self::expectException(BadValueException::class);
        self::expectExceptionMessageIsOrContains(
            'Разделитель ИНН и КПП не должен содержать цифры и заглавные латинские буквы'
        );

        $full_inn = $this->getCompanyFullInn();
        $invalid_separator = '1234';

        $full_inn->getValue($invalid_separator);
    }

    public function testCompanyGetValueInvalidEmptyStringSeparator(): void
    {
        self::expectException(BadValueException::class);
        self::expectExceptionMessageIsOrContains(
            'Разделитель ИНН и КПП не должен быть пустой строкой'
        );

        $full_inn = $this->getCompanyFullInn();
        $invalid_separator = '';

        $full_inn->getValue($invalid_separator);
    }

    public function testPersonGetValueWithInvalidLetterSeparator(): void
    {
        self::expectException(BadValueException::class);
        self::expectExceptionMessageIsOrContains(
            'Разделитель ИНН и КПП не должен содержать цифры и заглавные латинские буквы'
        );

        $full_inn = $this->getPersonFullInn();
        $invalid_separator = 'ABCD';

        $full_inn->getValue($invalid_separator);
    }

    public function testPersonGetValueInvalidNumericSeparator(): void
    {
        self::expectException(BadValueException::class);
        self::expectExceptionMessageIsOrContains(
            'Разделитель ИНН и КПП не должен содержать цифры и заглавные латинские буквы'
        );

        $full_inn = $this->getPersonFullInn();
        $invalid_separator = '1234';

        $full_inn->getValue($invalid_separator);
    }

    public function testPersonGetValueInvalidEmptyStringSeparator(): void
    {
        self::expectException(BadValueException::class);
        self::expectExceptionMessageIsOrContains(
            'Разделитель ИНН и КПП не должен быть пустой строкой'
        );

        $full_inn = $this->getPersonFullInn();
        $invalid_separator = '';

        $full_inn->getValue($invalid_separator);
    }

    public function testCompanyGetValueWithValidSeparator(): void
    {
        $full_inn  = $this->getCompanyFullInn();
        $separator = self::VALID_SEPARATOR;

        $full_inn->getValue($separator);

        self::assertNotEmpty($full_inn);
    }

    public function testPersonGetValueWithValidSeparator(): void
    {
        $full_inn  = $this->getPersonFullInn();
        $separator = self::VALID_SEPARATOR;

        $full_inn->getValue($separator);

        self::assertNotEmpty($full_inn);
    }

    public function testToString(): void
    {
        $inn_string = self::VALID_COMPANY_INN;
        $kpp_string = self::VALID_KPP;
        $separator  = FullInn::DEFAULT_SEPARATOR;

        $full_inn_string = $inn_string . $separator . $kpp_string;

        $full_inn = FullInn::createFromStrings($inn_string, $kpp_string);

        self::assertSame($full_inn_string, (string) $full_inn);
    }

    private function getCompanyFullInn(): FullInn
    {
        $inn_string = self::VALID_COMPANY_INN;
        $kpp_string = self::VALID_KPP;
        $full_inn   = FullInn::createFromStrings($inn_string, $kpp_string);

        return $full_inn;
    }

    private function getPersonFullInn(): FullInn
    {
        $inn_string = self::VALID_PERSON_INN;
        $full_inn   = FullInn::createFromStrings($inn_string);

        return $full_inn;
    }
}
