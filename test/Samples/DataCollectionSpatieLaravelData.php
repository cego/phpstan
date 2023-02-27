<?php

namespace Test\Samples;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Test\Samples\Enum\BackedEnumExample;

class DataCollectionSpatieLaravelData extends Data
{
    /**
     * @param DataCollection<int, BackedEnumExample> $collectionProperty
     */
    public function __construct(
        public readonly DataCollection $collectionProperty,
    ) {
    }

    public function initDefault(): self
    {
        return self::from(
            [
                'collectionProperty' => [],
            ],
        );
    }
}
