<?php

namespace Tests\Unit\Casts;

use App\Casts\MoneyCast;
use App\Models\Transaction;
use App\ValueObjects\Money;
use PHPUnit\Framework\TestCase;

class MoneyCastTest extends TestCase
{
    public function test_get_normalizes_any_driver_representation_to_a_money_instance(): void
    {
        $cast = new MoneyCast();
        $model = new Transaction();

        $this->assertTrue($cast->get($model, 'amount', 25, [])->equals(Money::fromDecimalString('25.00')));
        $this->assertTrue($cast->get($model, 'amount', 25.0, [])->equals(Money::fromDecimalString('25.00')));
        $this->assertTrue($cast->get($model, 'amount', '25.00', [])->equals(Money::fromDecimalString('25.00')));
        $this->assertNull($cast->get($model, 'amount', null, []));
    }

    public function test_set_always_stores_a_normalized_two_decimal_string(): void
    {
        $cast = new MoneyCast();
        $model = new Transaction();

        $this->assertSame('25.50', $cast->set($model, 'amount', Money::fromDecimalString('25,5'), []));
        $this->assertSame('25.50', $cast->set($model, 'amount', '25,5', []));
        $this->assertSame('25.00', $cast->set($model, 'amount', 25, []));
        $this->assertNull($cast->set($model, 'amount', null, []));
    }
}
