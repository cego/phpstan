<?php

namespace Test\Samples;

use Spatie\LaravelData\Data;
use Test\Samples\Enum\BackedEnumExample;

class CastedSpatieLaravelData extends Data
{
    public function __construct(
        public readonly BackedEnumExample $castedProperty,
    ) {
    }

    public function initDefault(): self
    {
        return self::from(
            [
                'castedProperty' => 'hello world', // A cast exists, so we don't actually know if this is legal or not
            ],
            [
                'castedProperty' => 123,          // A cast exists, so we don't actually know if this is legal or not | Maybe the cast converts the int into a string :shrug:
            ],
        );
    }
}
