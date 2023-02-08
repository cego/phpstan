<?php

namespace Test\Samples\Casts;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Casts\Uncastable;
use Spatie\LaravelData\Support\DataProperty;

class UncastableSpatieDataCast implements Cast, \Stringable
{
    public function cast(DataProperty $property, mixed $value, array $context): Collection
    {
        return Uncastable::create();
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return 'string';
    }
}
