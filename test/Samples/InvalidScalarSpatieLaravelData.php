<?php

namespace Test\Samples;

use Spatie\LaravelData\Data;

class InvalidScalarSpatieLaravelData extends Data
{
    public function __construct(
        public readonly string $stringProperty,
        public readonly int    $intProperty,
        public readonly bool   $booleanProperty,
        public readonly float  $floatProperty,
    ) {
    }

    public function initDefault(): self
    {
        return self::from([
            'stringProperty'  => null,
            'intProperty'     => 123.45,
            'booleanProperty' => [],
            'floatProperty'   => '12.5',
        ]);
    }
}
