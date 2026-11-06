<?php

namespace Tests\Unit\ValueObjects;

use App\ValueObjects\Money;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    public function test_parses_dot_and_comma_decimal_separators_to_the_same_value(): void
    {
        $this->assertTrue(Money::fromDecimalString('25.50')->equals(Money::fromDecimalString('25,50')));
    }

    public function test_the_ki17_scenario_amounts_with_different_decimal_place_counts_are_equal(): void
    {
        $this->assertTrue(Money::fromDecimalString('25,5')->equals(Money::fromDecimalString('25.50')));
        $this->assertSame(2550, Money::fromDecimalString('25,5')->cents());
        $this->assertSame(2550, Money::fromDecimalString('25.50')->cents());
    }

    public function test_the_ki18_scenario_int_float_and_string_inputs_are_equal(): void
    {
        $fromInt = Money::fromDecimalString(25);
        $fromFloat = Money::fromDecimalString(25.0);
        $fromString = Money::fromDecimalString('25.00');

        $this->assertTrue($fromInt->equals($fromFloat));
        $this->assertTrue($fromInt->equals($fromString));
    }

    public function test_round_trips_back_to_a_two_decimal_string(): void
    {
        $this->assertSame('25.50', Money::fromDecimalString('25,5')->toDecimalString());
        $this->assertSame('5.00', Money::fromDecimalString(5)->toDecimalString());
    }

    public function test_comparisons(): void
    {
        $twenty = Money::fromDecimalString('20.00');
        $twentyFive = Money::fromDecimalString('25.00');

        $this->assertTrue($twentyFive->greaterThanOrEqualTo($twenty));
        $this->assertTrue($twenty->lessThan($twentyFive));
        $this->assertFalse($twenty->greaterThanOrEqualTo($twentyFive));
    }

    public function test_add_and_subtract_use_exact_integer_cents_not_floats(): void
    {
        $a = Money::fromDecimalString('0.10');
        $b = Money::fromDecimalString('0.20');

        $this->assertSame('0.30', $a->add($b)->toDecimalString());
    }

    public function test_negative_amounts_round_trip_correctly(): void
    {
        $this->assertSame('-5.25', Money::fromDecimalString('-5,25')->toDecimalString());
    }

    public function test_non_numeric_input_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Money::fromDecimalString('not-a-number');
    }
}
