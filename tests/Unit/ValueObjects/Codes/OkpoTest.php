<?php

declare(strict_types=1);

namespace PhpRussianRequisites\Tests\Unit\ValueObjects\Codes;

use PhpRussianRequisites\Exceptions\BadValueException;
use PhpRussianRequisites\ValueObjects\Codes\Okpo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Okpo::class)]
final class OkpoTest extends TestCase
{
    private const VALID_COMPANY_OKPO_STRING      = '31637435';
    private const VALID_ENTREPRENEUR_OKPO_STRING = '0117254867';

    public function testTrimmingInputString(): void
    {
        $valid_okpo_string_with_spaces = ' ' . self::VALID_COMPANY_OKPO_STRING. ' ';

        $okpo = Okpo::createFromString($valid_okpo_string_with_spaces);

        self::assertNotEmpty($okpo);
    }

    public function testEmptyString(): void
    {
        self::expectException(BadValueException::class);
        self::expectExceptionMessageIsOrContains('не может быть пустой строкой');

        $empty_string = '';

        Okpo::createFromString($empty_string);
    }

    public function testInvalidSymbol(): void
    {
        self::expectException(BadValueException::class);
        self::expectExceptionMessageIsOrContains('должен содержать только цифры');

        $invalid_okpo_string = str_repeat('a', 10);

        Okpo::createFromString($invalid_okpo_string);
    }

    public function testInvalidLength(): void
    {
        self::expectException(BadValueException::class);
        self::expectExceptionMessageIsOrContains('должен иметь длину 8 или 10');

        $invalid_okpo_string = '123';

        okpo::createFromString($invalid_okpo_string);
    }

    public function testInvalidCompanyChecksum(): void
    {
        self::expectException(BadValueException::class);
        self::expectExceptionMessageIsOrContains('контрольная сумма кода ОКПО');

        $invalid_okpo_string = '76543213';

        Okpo::createFromString($invalid_okpo_string);
    }

    public function testInvalidEntrepreneurChecksum(): void
    {
        self::expectException(BadValueException::class);
        self::expectExceptionMessageIsOrContains('контрольная сумма кода ОКПО');

        $invalid_okpo_string = '7654321012';

        Okpo::createFromString($invalid_okpo_string);
    }

    public function testStringOfEightZeros(): void
    {
        self::expectException(BadValueException::class);

        $string_of_eight_zeros = str_repeat('0', 8);

        Okpo::createFromString($string_of_eight_zeros);
    }

    public function testStringOfTenZeros(): void
    {
        self::expectException(BadValueException::class);

        $string_of_ten_zeros = str_repeat('0', 10);

        Okpo::createFromString($string_of_ten_zeros);
    }

    public function testIsCompanyTrue(): void
    {
        $okpo = Okpo::createFromString(self::VALID_COMPANY_OKPO_STRING);

        self::assertTrue($okpo->isCompany());
    }

    public function testIsCompanyFalse(): void
    {
        $okpo = Okpo::createFromString(self::VALID_ENTREPRENEUR_OKPO_STRING);

        self::assertFalse($okpo->isCompany());
    }

    public function testIsEntrepreneurTrue(): void
    {
        $okpo = Okpo::createFromString(self::VALID_ENTREPRENEUR_OKPO_STRING);

        self::assertTrue($okpo->isEntrepreneur());
    }

    public function testIsEntrepreneurFalse(): void
    {
        $okpo = Okpo::createFromString(self::VALID_COMPANY_OKPO_STRING);

        self::assertFalse($okpo->isEntrepreneur());
    }

    public function testGetValue(): void
    {
        $okpo = Okpo::createFromString(self::VALID_COMPANY_OKPO_STRING);

        self::assertSame(self::VALID_COMPANY_OKPO_STRING, $okpo->getValue());
    }

    public function testToString(): void
    {
        $okpo_string = self::VALID_COMPANY_OKPO_STRING;

        $okpo = Okpo::createFromString($okpo_string);

        self::assertSame($okpo_string, (string) $okpo);
    }
}
