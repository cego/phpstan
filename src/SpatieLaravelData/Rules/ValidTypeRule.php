<?php

namespace Cego\phpstan\SpatieLaravelData\Rules;

use PhpParser\Node;
use PHPStan\Rules\Rule;
use PHPStan\Analyser\Scope;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use PHPStan\Rules\RuleError;
use Illuminate\Support\Collection;
use PHPStan\Node\CollectedDataNode;
use PHPStan\Rules\RuleErrorBuilder;
use Cego\phpstan\SpatieLaravelData\Collectors\FromCollector;
use Cego\phpstan\SpatieLaravelData\Collectors\CastCollector;
use Cego\phpstan\SpatieLaravelData\Collectors\ConstructorCollector;

class ValidTypeRule implements Rule
{
    /**
     * @phpstan-return class-string<TNodeType>
     */
    public function getNodeType(): string
    {
        return CollectedDataNode::class;
    }

    /**
     * @phpstan-param TNodeType $node
     * @return (string|RuleError)[] errors
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if ( ! $node instanceof CollectedDataNode) {
            return [];
        }

        $fromCollector = $node->get(FromCollector::class);

        $castCollector = collect($node->get(CastCollector::class))
            ->map(fn (array $castTypes) => $this->convertTypeListToString($castTypes[0]))
            ->flip()
            ->all();

        $classCollector = collect($node->get(ConstructorCollector::class))
            ->mapWithKeys(fn (array $data) => [$data[0]['class'] => $data[0]['properties']])                        // Outer key by class name
            ->map(fn (array $data) => collect($data)->mapWithKeys(fn (array $properties) => $properties)->all())    // Inner key by property name
            ->all();

        foreach ($fromCollector as $calls) {
            foreach ($calls as $call) {
                $errors[] = $this->compareTypes($call, $castCollector, $classCollector[$call['target']]);
            }
        }

        return Arr::flatten($errors);
    }

    private function compareTypes(array $call, array $casts, array $constructor): array
    {
        $errors = [];

        foreach ($call['types'] as $arrayList) {
            foreach ($arrayList as $key => $type) {
                // Ignore any additional data, since it does not matter
                if (! isset($constructor[$key])) {
                    continue;
                }

                $error = $this->checkType($call, $key, $type, $casts, $constructor[$key]);

                if ($error !== null) {
                    $errors[] = $error;
                }
            }
        }

        return $errors;
    }

    /**
     * @param array<int, array<int, string>> $typeList
     *
     * @return string
     */
    private function convertTypeListToString(array $typeList): string
    {
        return collect($typeList)
            // Sort the individual types within each intersection type and implode them.
            ->map(fn (array $intersectionList) => ltrim(collect($intersectionList)->sort()->implode('&'), '\\'))
            // Then sort the full intersection types, and implode them.
            ->sort()
            ->implode('|');
    }

    private function checkType(array $call, string $key, string $actualType, array $casts, array $expectedTypes): ?RuleError
    {
        // Ignore cases, where there exists a cast - since we cannot analyse them in debt
        if ($this->expectedTypesMatchesExactlyCast($casts, $expectedTypes)) {
            return null;
        }

        $actualTypeParts = Str::of($actualType)
            ->explode('|')
            ->map(fn (string $type) => explode('&', $type))
            ->all();

        foreach ($expectedTypes as $typeList) {
            if (empty($typeList)) {
                return null;
            }

            $validType = collect($typeList)
                ->reduce(fn (bool $result, string $expectedType) => $result && $this->isTypesMatching($actualTypeParts, $expectedType), true);

            if ($validType) {
                return null;
            }
        }

        return RuleErrorBuilder::message(sprintf('Argument $%s for %s::__construct() expects type [%s] but [%s] was given', $key, $call['target'], $this->expectedUnionTypesToString($expectedTypes), $actualType))
            ->line($call['method']['line'])
            ->file($call['method']['file'])
            ->build();
    }

    /**
     * Returns true if a cast exists exactly for the expected types.
     *
     * @param array $casts
     * @param array $expectedTypes
     *
     * @return bool
     */
    private function expectedTypesMatchesExactlyCast(array $casts, array $expectedTypes): bool
    {
        return isset($casts[$this->convertTypeListToString($expectedTypes)]);
    }

    /**
     * @param array<int, array<int, string>> $expectedTypes
     *
     * @return string
     */
    private function expectedUnionTypesToString(array $expectedTypes): string
    {
        if (empty($expectedTypes)) {
            return '';
        }

        $typeAsString = '';

        foreach ($expectedTypes as $expectedType) {
            $typeAsString .= '|'. $this->expectedIntersectionTypesToString($expectedType);
        }

        return ltrim($typeAsString, '|');
    }

    private function expectedIntersectionTypesToString(array $expectedTypes): string
    {
        return collect($expectedTypes)->implode('&');
    }

    private function isTypesMatching(array $actualTypeParts, string $parentType): bool
    {
        if ($parentType === 'mixed') {
            return true;
        }

        foreach ($actualTypeParts as $intersectionType) {
            $typeMatches = collect($intersectionType)
                ->reduce(fn (bool $result, string $type) => $result || $this->typeIsSubsetOf($type, $parentType), true);

            if ($typeMatches) {
                return true;
            }
        }

        return false;
    }

    private function typeIsSubsetOf(string $actualType, string $parentType): bool
    {
        if ($parentType === 'mixed') {
            return true;
        }

        if ($actualType === $parentType) {
            return true;
        }

        if (is_a($actualType, $parentType, true)) {
            return true;
        }

        return false;
    }
}
