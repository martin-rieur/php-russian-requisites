<?php

declare(strict_types=1);

namespace PhpRussianRequisites\Tests\Unit\ValueObjects\Codes;

use Override;
use PhpRussianRequisites\Exceptions\BadValueException;
use PhpRussianRequisites\ValueObjects\Codes\Grn;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Grn::class)]
final class GrnTest extends AbstractGrnTestCase
{
    #[Override]
    protected function grnClass(): string
    {
        return Grn::class;
    }

    #[Override]
    protected function validLength(): int
    {
        return 13;
    }

    #[Override]
    protected function getValidPrimaryGrnStrings(): array
    {
        return [
            '1234561234566',
            '5234561234562',
            '323456712345672',
        ];
    }

    #[Override]
    protected function getValidNotPrimaryGrnStrings(): array
    {
        return [
            '2213213213211',
            '6213213213218',
            '7213213213217',
            '8213213213216',
            '9213213213215',
            '423456712345672',
        ];
    }

    #[Override]
    protected function getGrnStringWithInvalidChecksum(): string
    {
        return str_repeat('1', 13);
    }

    #[Override]
    protected function getGrnStringWithInvalidRegionCode(): string
    {
        return '1230031231231';
    }

    #[Override]
    protected function getGrnStringsWithInvalidClassificationAttribute(): array
    {
        return [];
    }

    public function testInvalidLength(): void
    {
        $this->expectException(BadValueException::class);
        $this->expectExceptionMessageIsOrContains('должен иметь длину 13 или 15 цифр');

        $invalid_grn_string = str_repeat('1', 20);

        Grn::createFromString($invalid_grn_string);
    }

    public function testGetClassificationAttribute(): void
    {
        $valid_company_grn_strings = [
            '1' => '1234561234566',
            '2' => '2213213213211',
            '3' => '3213213213210',
            '4' => '4213213213210',
            '5' => '5234561234562',
            '6' => '6213213213218',
            '7' => '7213213213217',
            '8' => '8213213213216',
            '9' => '9213213213215',
        ];

        $valid_entrepreneur_grn_strings = [
            '1' => '123456712345678',
            '2' => '223456712345675',
            '3' => '323456712345672',
            '4' => '423456712345672',
            '5' => '523456712345679',
            '6' => '623456712345676',
            '7' => '723456712345673',
            '8' => '823456712345670',
            '9' => '923456712345670',
        ];

        foreach ($valid_company_grn_strings as $classification_attribute => $valid_grn_string) {
            $grn = Grn::createFromString($valid_grn_string);
            $ca  = (string) $classification_attribute;

            $this->assertSame($ca, $grn->getClassificationAttribute());
        }

        foreach ($valid_entrepreneur_grn_strings as $classification_attribute => $valid_grn_string) {
            $grn = Grn::createFromString($valid_grn_string);
            $ca  = (string) $classification_attribute;

            $this->assertSame($ca, $grn->getClassificationAttribute());
        }
    }

    public function testGetYear(): void
    {
        $grn_string = '1618374665248';
        $grn        = Grn::createFromString($grn_string);

        $this->assertSame('61', $grn->getYear());
    }

    public function testGetRegionCode(): void
    {
        $grn_string = '1618374665248';
        $grn        = Grn::createFromString($grn_string);

        $this->assertSame('83', $grn->getRegionCode());
    }

    public function testIsCompanyTrue(): void
    {
        $valid_company_grn_strings = [
            '1234561234566',
            '2213213213211',
            '5234561234562',
            '6213213213218',
            '7213213213217',
            '8213213213216',
            '9213213213215',
        ];

        foreach ($valid_company_grn_strings as $valid_grn_string) {
            $grn = Grn::createFromString($valid_grn_string);

            $this->assertTrue($grn->isCompany());
        }
    }

    public function testIsCompanyFalse(): void
    {
        $valid_entrepreneur_grn_strings = [
            '323456712345672',
            '423456712345672',
        ];

        foreach ($valid_entrepreneur_grn_strings as $valid_grn_string) {
            $grn = Grn::createFromString($valid_grn_string);

            $this->assertFalse($grn->isCompany());
        }
    }

    public function testIsEntrepreneurTrue(): void
    {
        $valid_entrepreneur_grn_strings = [
            '323456712345672',
            '423456712345672',
        ];

        foreach ($valid_entrepreneur_grn_strings as $valid_grn_string) {
            $grn = Grn::createFromString($valid_grn_string);

            $this->assertTrue($grn->isEntrepreneur());
        }
    }

    public function testIsEntrepreneurFalse(): void
    {
        $valid_company_grn_strings = [
            '1234561234566',
            '2213213213211',
            '5234561234562',
            '6213213213218',
            '7213213213217',
            '8213213213216',
            '9213213213215',
        ];

        foreach ($valid_company_grn_strings as $valid_grn_string) {
            $grn = Grn::createFromString($valid_grn_string);

            $this->assertFalse($grn->isEntrepreneur());
        }
    }
}
