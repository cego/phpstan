<?php

namespace Test\Samples;

use Stringable;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Casts\Cast;
use Test\Samples\Casts\UncastableSpatieDataCast;

class ValidComplexSpatieLaravelData extends Data
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
                'nullableMarkStringProperty' => null,
                'nullableTypeStringProperty' => null,
            ],
            [
                'nullableMarkStringProperty' => null,
                'nullableTypeStringProperty' => null,
                'intersectionProperty'       => new UncastableSpatieDataCast(),
            ],
        );
    }
}
