<?php

namespace Test\Samples;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Casts\Cast;

class SpatieLaravelData extends Data
{
    public string $stringProperty;
    public int $intProperty;
    public bool $booleanProperty;
    public Data $objectProperty;
    public ?Data $nullableObjectProperty;
    public $undefinedPropertyType;
    public EmptySpatieLaravelData|Cast $unionType;
    public Data&EmptySpatieLaravelData $intersectionType;

    public function __construct(
//        string                      $stringProperty,
//        int                         $intProperty,
//        bool                        $booleanProperty,
//        Data                        $objectProperty,
//        ?Data                       $nullableObjectProperty,
//                                    $undefinedPropertyType,
        EmptySpatieLaravelData|Cast $unionType,
//        Data&EmptySpatieLaravelData $intersectionType
    )
    {
//        $this->stringProperty = $stringProperty;
//        $this->intProperty = $intProperty;
//        $this->booleanProperty = $booleanProperty;
//        $this->objectProperty = $objectProperty;
//        $this->nullableObjectProperty = $nullableObjectProperty;
//        $this->undefinedPropertyType = $undefinedPropertyType;
        $this->unionType = $unionType;
//        $this->intersectionType = $intersectionType;
    }

    public function initDefault(): self
    {
        return self::from([
            'stringProperty'  => 'my string',
            'intProperty'     => 123,
            'booleanProperty' => 'cake',
            'objectProperty'  => new EmptySpatieLaravelData(),
        ], [
            'nullableObjectProperty' => null,
            'undefinedPropertyType'  => 'dsjahiuoas',
            'unionType'              => '',
            'intersectionType'       => 'sadsa',
        ]);
    }
}
