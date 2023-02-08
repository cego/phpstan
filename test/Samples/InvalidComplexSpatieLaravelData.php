<?php

namespace Test\Samples;

use Stringable;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Casts\Cast;

class InvalidComplexSpatieLaravelData extends Data
{
    public function __construct(
        public readonly ?string         $nullableMarkStringProperty,
        public readonly null|string     $nullableTypeStringProperty,
        public readonly Cast&Stringable $intersectionProperty,
    ) {
    }

    public function initDefault(): self
    {
        return self::from(
            [
                'nullableMarkStringProperty' => 123,
                'nullableTypeStringProperty' => 123.45,
            ],
            [
                'nullableMarkStringProperty' => [],
                'nullableTypeStringProperty' => [],
                'intersectionProperty'       => (object) [],
            ],
        );
    }
}
