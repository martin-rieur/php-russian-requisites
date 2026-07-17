<?php

declare(strict_types=1);

namespace PhpRussianRequisites\Tests\Unit\ValueObjects\Codes\Grn;

use Override;
use PhpRussianRequisites\ValueObjects\Codes\Grn\Ogrn;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Ogrn::class)]
final class OgrnTest extends AbstractGrnTestCase
{
    #[Override]
    protected function grnClass(): string
    {
        return Ogrn::class;
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
        ];
    }

    #[Override]
    protected function getValidNotPrimaryGrnStrings(): array
    {
        return [];
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
            '2213213213211',
            '3213213213210',
            '4213213213210',
            '6213213213218',
            '7213213213217',
            '8213213213216',
            '9213213213215',
        ];
    }

    public function testGetClassificationAttributeOne(): void
    {
        $ogrn_string = '1234561234566';
        $ogrn        = Ogrn::createFromString($ogrn_string);

        $this->assertSame('1', $ogrn->getClassificationAttribute());
    }

    public function testGetClassificationAttributeFive(): void
    {
        $ogrn_string = '5234561234562';
        $ogrn        = Ogrn::createFromString($ogrn_string);

        $this->assertSame('5', $ogrn->getClassificationAttribute());
    }

    public function testGetYear(): void
    {
        $ogrn_string = '1234561234566';
        $ogrn        = Ogrn::createFromString($ogrn_string);

        $this->assertSame('23', $ogrn->getYear());
    }

    public function testGetRegionCode(): void
    {
        $ogrn_string = '1234561234566';
        $ogrn        = Ogrn::createFromString($ogrn_string);

        $this->assertSame('45', $ogrn->getRegionCode());
    }
}
