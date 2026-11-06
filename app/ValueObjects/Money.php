<?php

namespace App\ValueObjects;

use InvalidArgumentException;

class Money
{
    private function __construct(private readonly int $cents)
    {
    }

    public static function fromCents(int $cents): self
    {
        return new self($cents);
    }

    /**
     * Accepts "25.50", "25,50", "25.5", "25,5", 25.5 (float) or 25 (int) and always
     * normalizes to an exact integer-cents representation - no float arithmetic involved,
     * so this is immune to both the KI-17 decimal-place mismatch and the KI-18 cross-driver
     * type mismatch (int/float/string) this value object exists to replace.
     */
    public static function fromDecimalString(string|int|float $amount): self
    {
        $normalized = str_replace(',', '.', (string) $amount);

        if (is_numeric($normalized) === false) {
            throw new InvalidArgumentException("'{$amount}' is not a valid monetary amount.");
        }

        $isNegative = str_starts_with($normalized, '-');
        $normalized = ltrim($normalized, '-');

        [$whole, $fraction] = array_pad(explode('.', $normalized, 2), 2, '0');
        $fraction = str_pad(substr($fraction, 0, 2), 2, '0');

        $cents = ((int) $whole * 100) + (int) $fraction;

        return new self($isNegative ? -$cents : $cents);
    }

    public function cents(): int
    {
        return $this->cents;
    }

    public function toDecimalString(): string
    {
        $sign = $this->cents < 0 ? '-' : '';
        $absCents = abs($this->cents);

        return sprintf('%s%d.%02d', $sign, intdiv($absCents, 100), $absCents % 100);
    }

    public function equals(Money $other): bool
    {
        return $this->cents === $other->cents;
    }

    public function greaterThanOrEqualTo(Money $other): bool
    {
        return $this->cents >= $other->cents;
    }

    public function lessThan(Money $other): bool
    {
        return $this->cents < $other->cents;
    }

    public function add(Money $other): self
    {
        return new self($this->cents + $other->cents);
    }

    public function subtract(Money $other): self
    {
        return new self($this->cents - $other->cents);
    }

    public function __toString(): string
    {
        return $this->toDecimalString();
    }
}
