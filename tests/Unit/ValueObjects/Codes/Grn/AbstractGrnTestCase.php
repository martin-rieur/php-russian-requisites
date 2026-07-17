<?php

declare(strict_types=1);

namespace PhpRussianRequisites\Tests\Unit\ValueObjects\Codes\Grn;

use PhpRussianRequisites\Exceptions\BadValueException;
use PhpRussianRequisites\Interfaces\ExceptionInterface;
use PhpRussianRequisites\ValueObjects\Codes\Grn\Grn;
use PHPUnit\Framework\TestCase;

abstract class AbstractGrnTestCase extends TestCase
{
    /**
     * @return class-string<Grn>
     */
    abstract protected function grnClass(): string;
    abstract protected function validLength(): int;
    /**
     * Строки с валидным основным ГРН (ОГРН или ОГРНИП)
     *
     * @return string[]
     */
    abstract protected function getValidPrimaryGrnStrings(): array;
    /**
     * Строки с валидными неосновным ГРН
     *
     * @return string[]
     */
    abstract protected function getValidNotPrimaryGrnStrings(): array;
    abstract protected function getGrnStringWithInvalidChecksum(): string;
    abstract protected function getGrnStringWithInvalidRegionCode(): string;
    /**
     * Строки с ГРН с некорректными признаками отнесения
     *
     * @return string[]
     */
    abstract protected function getGrnStringsWithInvalidClassificationAttribute(): array;

    public function testEmptyString(): void
    {
        $this->expectException(BadValueException::class);
        $this->expectExceptionMessageIsOrContains('не может быть пустой строкой');

        $empty_string = '';

        $this->grnClass()::createFromString($empty_string);
    }

    public function testStringOfThirteenZeros(): void
    {
        $this->expectException(BadValueException::class);

        $string_of_thirteen_zeros = str_repeat('0', $this->validLength());

        $this->grnClass()::createFromString($string_of_thirteen_zeros);
    }

    public function testInvalidSymbol(): void
    {
        $this->expectException(BadValueException::class);
        $this->expectExceptionMessageIsOrContains('должен содержать только цифры');

        $invalid_grn_string = str_repeat('A', $this->validLength());

        $this->grnClass()::createFromString($invalid_grn_string);
    }

    public function testTrimmingInputString(): void
    {
        $valid_grn_string = '  ' . $this->getValidGrnString() . '  ';

        $grn = $this->grnClass()::createFromString($valid_grn_string);

        $this->assertNotEmpty($grn);
    }

    public function testInvalidLength(): void
    {
        $this->expectException(BadValueException::class);
        $this->expectExceptionMessageIsOrContains('должен иметь длину ' . $this->validLength() . ' цифр');

        $invalid_grn_string = str_repeat('1', $this->validLength() + 3);

        $this->grnClass()::createFromString($invalid_grn_string);
    }

    public function testInvalidChecksum(): void
    {
        $this->expectException(BadValueException::class);
        $this->expectExceptionMessageIsOrContains('Неверная контрольная сумма');

        $invalid_grn_string = $this->getGrnStringWithInvalidChecksum();

        $this->grnClass()::createFromString($invalid_grn_string);
    }

    public function testInvalidRegionCode(): void
    {
        $this->expectException(BadValueException::class);
        $this->expectExceptionMessageIsOrContains('неверно указан код региона РФ');

        $invalid_grn_string = $this->getGrnStringWithInvalidRegionCode();

        $this->grnClass()::createFromString($invalid_grn_string);
    }

    public function testInvalidClassificationAttribute(): void
    {
        $invalid_grn_strings = $this->getGrnStringsWithInvalidClassificationAttribute();

        $this->testInvalidGrnStrings(
            BadValueException::class,
            'Неверно указан признак отнесения',
            ...$invalid_grn_strings,
        );
    }

    public function testIsPrimaryFalse(): void
    {
        $valid_grn_strings = $this->getValidNotPrimaryGrnStrings();

        if (0 === count($valid_grn_strings)) {
            $this->markTestSkipped();
        }

        foreach ($valid_grn_strings as $valid_grn_string) {
            $grn = $this->grnClass()::createFromString($valid_grn_string);

            $this->assertFalse($grn->isPrimary());
        }
    }

    public function testIsPrimaryTrue(): void
    {
        $valid_grn_strings = $this->getValidPrimaryGrnStrings();

        if (0 === count($valid_grn_strings)) {
            $this->markTestSkipped();
        }

        foreach ($valid_grn_strings as $valid_grn_string) {
            $grn = $this->grnClass()::createFromString($valid_grn_string);

            $this->assertTrue($grn->isPrimary());
        }
    }

    public function testGetValue(): void
    {
        $grn_string = $this->getValidGrnString();
        $grn        = $this->grnClass()::createFromString($grn_string);

        $this->assertSame($grn_string, $grn->getValue());
    }

    public function testToString(): void
    {
        $grn_string = $this->getValidGrnString();
        $grn        = $this->grnClass()::createFromString($grn_string);

        $this->assertSame($grn_string, (string) $grn);
    }

    /**
     * @param class-string $exception_class_string
     */
    private function testInvalidGrnStrings(
        string $exception_class_string,
        string $exception_message,
        string ...$invalid_grn_strings
    ): void {
        $catches_count = 0;

        foreach ($invalid_grn_strings as $invalid_grn_string) {
            try {
                $this->grnClass()::createFromString($invalid_grn_string);
            } catch (ExceptionInterface $th) {
                if (
                    $th instanceof $exception_class_string
                    && str_contains($th->getMessage(), $exception_message)
                ) {
                    $catches_count++;
                }
            }
        }

        $this->assertSame(count($invalid_grn_strings), $catches_count);
    }

    final protected function getValidGrnString(): string
    {
        $valid_primary_grn_strings     = $this->getValidPrimaryGrnStrings();
        $valid_non_primary_grn_strings = $this->getValidPrimaryGrnStrings();

        $valid_grn_strings = array_merge(
            $valid_primary_grn_strings,
            $valid_non_primary_grn_strings,
        );

        if (0 === count($valid_grn_strings)) {
            throw new \Exception('Не указан ни один валидный ГРН');
        }

        $first_key = array_key_first($valid_grn_strings);

        return $valid_grn_strings[$first_key];
    }
}
