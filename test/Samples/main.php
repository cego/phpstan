<?php

namespace Test;

use Illuminate\Support\Collection;
use Test\Samples\SpatieLaravelData;
use Test\Samples\EmptySpatieLaravelData;

/** @var Collection<int, string> $bla */
$bla = "";

SpatieLaravelData::from([
    'intProperty'            => 12,
    'stringProperty'         => 'hohoho',
], [
    'objectProperty'         => new EmptySpatieLaravelData(),
    'nullableObjectProperty' => null,
    'unionType'              => (fn () => $bla)(),
]);

