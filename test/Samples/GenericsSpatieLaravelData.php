<?php

namespace Test\Samples;

use Spatie\LaravelData\Data;

class GenericsSpatieLaravelData extends Data
{
    public function __construct(
        public readonly EmptySpatieLaravelData $genericProperty,
    ) {
    }

    public function initDefault(): self
    {
        /** @var EmptySpatieLaravelData<string> $var */
        $var = '';

        return self::from([
            'genericProperty' => $var,
        ]);
    }
}
