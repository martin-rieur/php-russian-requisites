<?php

declare(strict_types=1);

namespace PhpRussianRequisites\Tests\Unit\ValueObjects\Codes\Grn;

use Override;
use PhpRussianRequisites\Exceptions\TypeCastingException;
use PhpRussianRequisites\ValueObjects\Codes\Grn\EntrepreneurGrn;
use PhpRussianRequisites\ValueObjects\Codes\Grn\Ogrnip;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(EntrepreneurGrn::class)]
#[UsesClass(Ogrnip::class)]
final class EntrepreneurGrnTest extends AbstractGrnTestCase
{
    #[Override]
    protected function grnClass(): string
    {
        return EntrepreneurGrn::class;
    }

    #[Override]
    protected function validLength(): int
    {
        return 15;
    }

    #[Override]
    protected function getValidPrimaryGrnStrings(): array
    {
        return [
            '368137541618722'
        ];
    }

    #[Override]
    protected function getValidNotPrimaryGrnStrings(): array
    {
        return [
            '423456712345672',
        ];
    }

    #[Override]
    protected function getGrnStringWithInvalidChecksum(): string
    {
        return '321321321321320';
    }

    #[Override]
    protected function getGrnStringWithInvalidRegionCode(): string
    {
        return '321001321321322';
    }

    #[Override]
    protected function getGrnStringsWithInvalidClassificationAttribute(): array
    {
        return [
            '123456712345678',
            '223456712345675',
            '523456712345679',
            '623456712345676',
            '723456712345673',
            '823456712345670',
            '923456712345670',
        ];
    }

    public function testGetClassificationAttributeThree(): void
    {
        $grn_string = '323456712345672';
        $grn        = EntrepreneurGrn::createFromString($grn_string);

        self::assertSame('3', $grn->getClassificationAttribute());
    }

    public function testGetClassificationAttributeFour(): void
    {
        $grn_string = '423456712345672';
        $grn        = EntrepreneurGrn::createFromString($grn_string);

        self::assertSame('4', $grn->getClassificationAttribute());
    }

    public function testCastingToPrimaryFailure(): void
    {
        self::expectException(TypeCastingException::class);

        $valid_grn_string = '423456712345672';
        $grn              = EntrepreneurGrn::createFromString($valid_grn_string);

        $grn->toPrimary();
    }

    public function testToPrimary(): void
    {
        $grn_string = '323456712345672';
        $grn        = EntrepreneurGrn::createFromString($grn_string);

        self::assertInstanceOf(Ogrnip::class, $grn->toPrimary());
    }

    public function testGetYear(): void
    {
        $grn_string = '368137541618722';
        $grn        = EntrepreneurGrn::createFromString($grn_string);

        self::assertSame('68', $grn->getYear());
    }

    public function testGetRegionCode(): void
    {
        $grn_string = '368137541618722';
        $grn        = EntrepreneurGrn::createFromString($grn_string);

        self::assertSame('13', $grn->getRegionCode());
    }
}
