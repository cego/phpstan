<?php

namespace Test\Samples;

use Spatie\LaravelData\Data;
use Test\Samples\Enum\BackedEnumExample;

class InvalidNullableCastedSpatieLaravelData extends Data
{
    public function __construct(
        public readonly BackedEnumExample $castedProperty,
    ) {
    }

    public function initDefault(): self
    {
        self::from(
            [
                'castedProperty' => null, // We should know for certain that null is not accepted here
            ],
        );
    }
}
