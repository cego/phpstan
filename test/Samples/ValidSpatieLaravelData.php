<?php

namespace Test\Samples;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Casts\Cast;
use Test\Samples\Casts\UncastableSpatieDataCast;

class ValidSpatieLaravelData extends Data
{
    public function __construct(
        public readonly string                      $stringProperty,
        public readonly int                         $intProperty,
        public readonly bool                        $booleanProperty,
        public readonly float                       $floatProperty,
        public readonly float                       $float2Property,
        public readonly Data                        $objectProperty,
        public readonly ?Data                       $nullableObjectProperty,
        public readonly mixed                       $mixedPropertyType,
        public readonly EmptySpatieLaravelData|Cast $unionType,
        public readonly Data&EmptySpatieLaravelData $intersectionType
    ) {
    }

    public function initDefault(): self
    {
        return self::from([
            'stringProperty'  => 'my string',
            'intProperty'     => 123,
            'floatProperty'   => 123.45,
            'float2Property'  => 123, // int is smaller than float, so is always compatible.
            'booleanProperty' => true,
            'objectProperty'  => new EmptySpatieLaravelData(),
        ], [
            'nullableObjectProperty' => null,
            'mixedPropertyType'      => 'dsjahiuoas',
            'unionType'              => new UncastableSpatieDataCast(),
            'intersectionType'       => new EmptySpatieLaravelData(),
        ]);
    }
}
