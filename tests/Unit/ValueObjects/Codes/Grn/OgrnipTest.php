<?php

declare(strict_types=1);

namespace PhpRussianRequisites\Tests\Unit\ValueObjects\Codes\Grn;

use Override;
use PhpRussianRequisites\ValueObjects\Codes\Grn\Ogrnip;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Ogrnip::class)]
final class OgrnipTest extends AbstractGrnTestCase
{
    #[Override]
    protected function grnClass(): string
    {
        return Ogrnip::class;
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
            '323456712345672',
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
            '423456712345672',
            '523456712345679',
            '623456712345676',
            '723456712345673',
            '823456712345670',
            '923456712345670',
        ];
    }

    public function testGetClassificationAttributeThree(): void
    {
        $ogrnip_string = '323456712345672';
        $ogrnip        = Ogrnip::createFromString($ogrnip_string);

        self::assertSame('3', $ogrnip->getClassificationAttribute());
    }

    public function testGetYear(): void
    {
        $ogrnip_string = '323456712345672';
        $ogrnip        = Ogrnip::createFromString($ogrnip_string);

        self::assertSame('23', $ogrnip->getYear());
    }

    public function testGetRegionCode(): void
    {
        $ogrnip_string = '323456712345672';
        $ogrnip        = Ogrnip::createFromString($ogrnip_string);

        self::assertSame('45', $ogrnip->getRegionCode());
    }
}
