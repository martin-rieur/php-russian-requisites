<?php

declare(strict_types=1);

namespace PhpRussianRequisites\Tests\Unit\ValueObjects\Codes;

use PhpRussianRequisites\Exceptions\BadValueException;
use PhpRussianRequisites\ValueObjects\Codes\Inn;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Inn::class)]
final class InnTest extends TestCase
{
    public const string VALID_COMPANY_INN = '9876543210';
    public const string VALID_PERSON_INN  = '987654321018';

    public function testTrimmingInputString(): void
    {
        $valid_inn_string_with_spaces = ' ' . self::VALID_COMPANY_INN . ' ';

        $inn = Inn::createFromString($valid_inn_string_with_spaces);

        $this->assertNotEmpty($inn);
    }

    public function testEmptyString(): void
    {
        $this->expectException(BadValueException::class);
        $this->expectExceptionMessageIsOrContains('не может быть пустой строкой');

        $empty_string = '';

        Inn::createFromString($empty_string);
    }

    public function testInvalidSymbol(): void
    {
        $this->expectException(BadValueException::class);
        $this->expectExceptionMessageIsOrContains('только из цифр');

        $invalid_inn_string = str_repeat('a', 10);

        Inn::createFromString($invalid_inn_string);
    }

    public function testInvalidLength(): void
    {
        $this->expectException(BadValueException::class);
        $this->expectExceptionMessageIsOrContains('должен иметь длину 10 или 12');

        $invalid_inn_string = '123';

        Inn::createFromString($invalid_inn_string);
    }

    public function testInvalidCompanyChecksum(): void
    {
        $this->expectException(BadValueException::class);
        $this->expectExceptionMessageIsOrContains('контрольная сумма ИНН юридического лица');

        $invalid_inn_string = '9876543213';

        Inn::createFromString($invalid_inn_string);
    }

    public function testInvalidPersonChecksum(): void
    {
        $this->expectException(BadValueException::class);
        $this->expectExceptionMessageIsOrContains('контрольная сумма ИНН физического лица');

        $invalid_inn_string = '987654321012';

        Inn::createFromString($invalid_inn_string);
    }

    public function testInvalidRegionCode(): void
    {
        $this->expectException(BadValueException::class);
        $this->expectExceptionMessageIsOrContains('первые две цифры не могут быть 00');

        $invalid_inn_string = '0012345673';

        Inn::createFromString($invalid_inn_string);
    }

    public function testStringOfTenZeros(): void
    {
        $this->expectException(BadValueException::class);

        $string_of_ten_zeros = str_repeat('0', 10);

        Inn::createFromString($string_of_ten_zeros);
    }

    public function testStringOfTwelveZeros(): void
    {
        $this->expectException(BadValueException::class);

        $string_of_twelve_zeros = str_repeat('0', 12);

        Inn::createFromString($string_of_twelve_zeros);
    }

    public function testGetValue(): void
    {
        $inn = Inn::createFromString(self::VALID_COMPANY_INN);

        $this->assertSame(self::VALID_COMPANY_INN, $inn->getValue());
    }

    public function testGetRegionCode(): void
    {
        $inn = Inn::createFromString(self::VALID_COMPANY_INN);

        $this->assertSame('98', $inn->getRegionCode());
    }

    public function testIsCompanyTrue(): void
    {
        $inn = Inn::createFromString(self::VALID_COMPANY_INN);

        $this->assertTrue($inn->isCompany());
    }

    public function testIsCompanyFalse(): void
    {
        $inn = Inn::createFromString(self::VALID_PERSON_INN);

        $this->assertFalse($inn->isCompany());
    }

    public function testIsPersonTrue(): void
    {
        $inn = Inn::createFromString(self::VALID_PERSON_INN);

        $this->assertTrue($inn->isPerson());
    }

    public function testIsPersonFalse(): void
    {
        $inn = Inn::createFromString(self::VALID_COMPANY_INN);

        $this->assertFalse($inn->isPerson());
    }

    public function testToString(): void
    {
        $inn_string = self::VALID_COMPANY_INN;

        $inn = Inn::createFromString($inn_string);

        $this->assertSame($inn_string, (string) $inn);
    }
}
