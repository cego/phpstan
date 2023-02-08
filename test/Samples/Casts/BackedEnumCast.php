<?php

namespace Test\Samples\Casts;

use BackedEnum;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Casts\Uncastable;
use Spatie\LaravelData\Support\DataProperty;

class BackedEnumCast implements Cast
{
    public function cast(DataProperty $property, mixed $value, array $context): BackedEnum|Uncastable
    {
        return Uncastable::create();
    }
}
