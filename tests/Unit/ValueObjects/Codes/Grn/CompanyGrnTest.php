<?php

declare(strict_types=1);

namespace PhpRussianRequisites\Tests\Unit\ValueObjects\Codes\Grn;

use Override;
use PhpRussianRequisites\Exceptions\TypeCastingException;
use PhpRussianRequisites\ValueObjects\Codes\Grn\CompanyGrn;
use PhpRussianRequisites\ValueObjects\Codes\Grn\Ogrn;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(CompanyGrn::class)]
#[UsesClass(Ogrn::class)]
final class CompanyGrnTest extends AbstractGrnTestCase
{
    #[Override]
    protected function grnClass(): string
    {
        return CompanyGrn::class;
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
            '5169724687116'
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
        ];
    }

    #[Override]
    protected function getGrnStringWithInvalidChecksum(): string
    {
        return str_repeat('1', $this->validLength());
    }

    #[Override]
    protected function getGrnStringWithInvalidRegionCode(): string
    {
        return '1230031231231';
    }

    #[Override]
    protected function getGrnStringsWithInvalidClassificationAttribute(): array
    {
        return [
            '3213213213210',
            '4213213213210',
        ];
    }

    public function testGetClassificationAttributeOne(): void
    {
        $grn_string = '1234561234566';
        $grn        = CompanyGrn::createFromString($grn_string);

        self::assertSame('1', $grn->getClassificationAttribute());
    }

    public function testGetClassificationAttributeTwo(): void
    {
        $grn_string = '2213213213211';
        $grn        = CompanyGrn::createFromString($grn_string);

        self::assertSame('2', $grn->getClassificationAttribute());
    }

    public function testGetClassificationAttributeFive(): void
    {
        $grn_string = '5234561234562';
        $grn        = CompanyGrn::createFromString($grn_string);

        self::assertSame('5', $grn->getClassificationAttribute());
    }

    public function testGetClassificationAttributeSix(): void
    {
        $grn_string = '6213213213218';
        $grn        = CompanyGrn::createFromString($grn_string);

        self::assertSame('6', $grn->getClassificationAttribute());
    }

    public function testGetClassificationAttributeSeven(): void
    {
        $grn_string = '7213213213217';
        $grn        = CompanyGrn::createFromString($grn_string);

        self::assertSame('7', $grn->getClassificationAttribute());
    }

    public function testGetClassificationAttributeEight(): void
    {
        $grn_string = '8213213213216';
        $grn        = CompanyGrn::createFromString($grn_string);

        self::assertSame('8', $grn->getClassificationAttribute());
    }

    public function testGetClassificationAttributeNine(): void
    {
        $grn_string = '9213213213215';
        $grn        = CompanyGrn::createFromString($grn_string);

        self::assertSame('9', $grn->getClassificationAttribute());
    }

    public function testToPrimaryFailure(): void
    {
        $valid_grn_strings = $this->getValidNotPrimaryGrnStrings();

        $catches_count = 0;

        foreach ($valid_grn_strings as $valid_grn_string) {
            $grn = CompanyGrn::createFromString($valid_grn_string);

            try {
                $grn->toPrimary();
            } catch (TypeCastingException) {
                $catches_count++;
            }
        }

        self::assertSame(count($valid_grn_strings), $catches_count);
    }

    public function testToPrimary(): void
    {
        $valid_grn_strings = $this->getValidPrimaryGrnStrings();

        foreach ($valid_grn_strings as $valid_grn_string) {
            $grn = CompanyGrn::createFromString($valid_grn_string);

            self::assertInstanceOf(Ogrn::class, $grn->toPrimary());
        }
    }

    public function testGetYear(): void
    {
        $grn_string = '5169724687116';
        $grn        = CompanyGrn::createFromString($grn_string);

        self::assertSame('16', $grn->getYear());
    }

    public function testGetRegionCode(): void
    {
        $grn_string = '5169724687116';
        $grn        = CompanyGrn::createFromString($grn_string);

        self::assertSame('97', $grn->getRegionCode());
    }
}
