<?php

use Cego\CegoFixer;

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/test');

return CegoFixer::applyRules($finder, [
    'ternary_to_null_coalescing' => true,
]);
