<?php

declare(strict_types=1);

namespace PhpRussianRequisites\Tests\Unit\ValueObjects\Codes;

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
final class FullInnTest extends TestCase
{
    private const string VALID_COMPANY_INN = '9876543210';
    private const string VALID_PERSON_INN  = '987654321018';
    private const string VALID_KPP         = '35234V111';

    public function testCompanyInnWithoutKpp(): void
    {
        $this->expectException(ViolationOfInternalDataConsistencyException::class);
        $this->expectExceptionMessageIsOrContains('У юридических лиц должен быть указан КПП');

        $inn_string = self::VALID_COMPANY_INN;

        FullInn::createFromStrings($inn_string);
    }

    public function testPersonInnWithKpp(): void
    {
        $this->expectException(ViolationOfInternalDataConsistencyException::class);
        $this->expectExceptionMessageIsOrContains('У физических лиц не должен быть указан КПП');

        $inn_string = self::VALID_PERSON_INN;
        $kpp_string = self::VALID_KPP;

        FullInn::createFromStrings($inn_string, $kpp_string);
    }

    public function testCompanyInnWithKpp(): void
    {
        $inn_string = self::VALID_COMPANY_INN;
        $kpp_string = self::VALID_KPP;

        $full_inn = FullInn::createFromStrings($inn_string, $kpp_string);

        $this->assertNotEmpty($full_inn);
    }

    public function testPersonInnWithoutKpp(): void
    {
        $inn_string = self::VALID_PERSON_INN;

        $full_inn = FullInn::createFromStrings($inn_string);

        $this->assertNotEmpty($full_inn);
    }

    public function testEmptyStringInsteadInn(): void
    {
        $this->expectException(ViolationOfInternalDataConsistencyException::class);
        $this->expectExceptionMessageIsOrContains('Должен быть указан ИНН');

        $empty_string = '';

        FullInn::createFromStrings($empty_string);
    }

    public function testEmptyStringInsteadInnWithKpp(): void
    {
        $this->expectException(ViolationOfInternalDataConsistencyException::class);
        $this->expectExceptionMessageIsOrContains('Должен быть указан ИНН');

        $empty_string = '';
        $kpp_string   = self::VALID_KPP;

        FullInn::createFromStrings($empty_string, $kpp_string);
    }

    public function testSpaceInsteadInnWithoutKpp(): void
    {
        $this->expectException(ViolationOfInternalDataConsistencyException::class);
        $this->expectExceptionMessageIsOrContains('Должен быть указан ИНН');

        $space = ' ';

        FullInn::createFromStrings($space);
    }

    public function testSpaceInsteadInnWithKpp(): void
    {
        $this->expectException(ViolationOfInternalDataConsistencyException::class);
        $this->expectExceptionMessageIsOrContains('Должен быть указан ИНН');

        $space      = ' ';
        $kpp_string = self::VALID_KPP;

        FullInn::createFromStrings($space, $kpp_string);
    }
}
