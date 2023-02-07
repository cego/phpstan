<?php

namespace Test\Samples;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Support\DataProperty;

class SpatieDataCast implements Cast
{
    public function cast(DataProperty $property, mixed $value, array $context)
    {
        return new EmptySpatieLaravelData();
    }
}
