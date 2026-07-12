<?php

declare(strict_types=1);

namespace PhpRussianRequisites\Tests\Unit\ValueObjects\Codes;

use PhpRussianRequisites\Exceptions\BadValueException;
use PhpRussianRequisites\ValueObjects\Codes\Kpp;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Kpp::class)]
final class KppTest extends TestCase
{
    private const string VALID_KPP = '35234V111';

    public function testTrimmingInputString(): void
    {
        $valid_kpp_string_with_spaces = ' ' . self::VALID_KPP . ' ';

        $kpp = Kpp::createFromString($valid_kpp_string_with_spaces);

        $this->assertNotEmpty($kpp);
    }

    public function testEmptyString(): void
    {
        $this->expectException(BadValueException::class);
        $this->expectExceptionMessageIsOrContains('не может быть пустой строкой');

        $empty_string = '';

        Kpp::createFromString($empty_string);
    }

    public function testInvalidSymbol(): void
    {
        $this->expectException(BadValueException::class);
        $this->expectExceptionMessageIsOrContains('только цифры и заглавные латинские буквы');

        $invalid_kpp_string = '123-56789';

        Kpp::createFromString($invalid_kpp_string);
    }

    public function testInvalidLength(): void
    {
        $this->expectException(BadValueException::class);
        $this->expectExceptionMessageIsOrContains('должен состоять из 9 символов');

        $invalid_kpp_string = '1235678';

        Kpp::createFromString($invalid_kpp_string);
    }

    public function testInvalidFormatWithLetterInFirstPosition(): void
    {
        $this->expectException(BadValueException::class);
        $this->expectExceptionMessageIsOrContains('Неверный формат');

        $invalid_kpp_string = 'A23456789';

        Kpp::createFromString($invalid_kpp_string);
    }

    public function testInvalidFormatWithLetterInSecondPosition(): void
    {
        $this->expectException(BadValueException::class);
        $this->expectExceptionMessageIsOrContains('Неверный формат');

        $invalid_kpp_string = '1B3456789';

        Kpp::createFromString($invalid_kpp_string);
    }

    public function testInvalidFormatWithLetterInThirdPosition(): void
    {
        $this->expectException(BadValueException::class);
        $this->expectExceptionMessageIsOrContains('Неверный формат');

        $invalid_kpp_string = '12C456789';

        Kpp::createFromString($invalid_kpp_string);
    }

    public function testInvalidFormatWithLetterInFourthPosition(): void
    {
        $this->expectException(BadValueException::class);
        $this->expectExceptionMessageIsOrContains('Неверный формат');

        $invalid_kpp_string = '123D56789';

        Kpp::createFromString($invalid_kpp_string);
    }

    public function testInvalidFormatWithLetterInSeventhPosition(): void
    {
        $this->expectException(BadValueException::class);
        $this->expectExceptionMessageIsOrContains('Неверный формат');

        $invalid_kpp_string = '123456G89';

        Kpp::createFromString($invalid_kpp_string);
    }

    public function testInvalidFormatWithLetterInEighthPosition(): void
    {
        $this->expectException(BadValueException::class);
        $this->expectExceptionMessageIsOrContains('Неверный формат');

        $invalid_kpp_string = '1234567H9';

        Kpp::createFromString($invalid_kpp_string);
    }

    public function testInvalidFormatWithLetterInNinthPosition(): void
    {
        $this->expectException(BadValueException::class);
        $this->expectExceptionMessageIsOrContains('Неверный формат');

        $invalid_kpp_string = '12345678I';

        Kpp::createFromString($invalid_kpp_string);
    }

    public function testInvalidRegionCode(): void
    {
        $this->expectException(BadValueException::class);
        $this->expectExceptionMessageIsOrContains('код региона');

        $invalid_kpp_string = '001234567';

        Kpp::createFromString($invalid_kpp_string);
    }

    public function testStringOfNineZeros(): void
    {
        $this->expectException(BadValueException::class);

        $string_of_nine_zeros = str_repeat('0', 9);

        Kpp::createFromString($string_of_nine_zeros);
    }

    public function testInvalidReasonForRegistration(): void
    {
        $this->expectException(BadValueException::class);
        $this->expectExceptionMessageIsOrContains('причина постановки на учёт');

        $invalid_kpp_string = '123400789';

        Kpp::createFromString($invalid_kpp_string);
    }

    public function testValidFormatWithoutLetters(): void
    {
        $valid_kpp_string = '123456789';

        $kpp = Kpp::createFromString($valid_kpp_string);

        $this->assertNotEmpty($kpp);
    }

    public function testValidFormatWithLetterInFifthPosition(): void
    {
        $valid_kpp_string = '1234A6789';

        $kpp = Kpp::createFromString($valid_kpp_string);

        $this->assertNotEmpty($kpp);
    }

    public function testValidFormatWithLetterInSixthPosition(): void
    {
        $valid_kpp_string = '12345Z789';

        $kpp = Kpp::createFromString($valid_kpp_string);

        $this->assertNotEmpty($kpp);
    }

    public function testValidFormatWithLetterInFifthAndSixthPositions(): void
    {
        $valid_kpp_string = '1234FS789';

        $kpp = Kpp::createFromString($valid_kpp_string);

        $this->assertNotEmpty($kpp);
    }

    public function testGetValue(): void
    {
        $kpp = Kpp::createFromString(self::VALID_KPP);

        $this->assertSame(self::VALID_KPP, $kpp->getValue());
    }

    public function testGetRegionCode(): void
    {
        $kpp = Kpp::createFromString(self::VALID_KPP);

        $this->assertSame('35', $kpp->getRegionCode());
    }

    public function testGetReasonForRegistration(): void
    {
        $kpp = Kpp::createFromString(self::VALID_KPP);

        $this->assertSame('4V', $kpp->getReasonForRegistration());
    }

    public function testToString(): void
    {
        $kpp_string = self::VALID_KPP;

        $kpp = Kpp::createFromString($kpp_string);

        $this->assertSame($kpp_string, (string) $kpp);
    }
}
