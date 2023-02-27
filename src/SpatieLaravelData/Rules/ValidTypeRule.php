<?php

namespace Cego\phpstan\SpatieLaravelData\Rules;

use PhpParser\Node;
use PHPStan\Rules\Rule;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\RuleError;
use PHPStan\Node\CollectedDataNode;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;
use Cego\phpstan\TypeSystem\UnionType;
use Spatie\LaravelData\DataCollection;
use Cego\phpstan\TypeSystem\TypeSystem;
use Cego\phpstan\SpatieLaravelData\Data\Call;
use Cego\phpstan\SpatieLaravelData\Data\Constructor;
use Cego\phpstan\SpatieLaravelData\Collectors\CastCollector;
use Cego\phpstan\SpatieLaravelData\Collectors\FromCollector;
use Cego\phpstan\SpatieLaravelData\Collectors\ConstructorCollector;

class ValidTypeRule implements Rule
{
    /**
     * Returns the node type this rule should trigger for
     *
     * @phpstan-return class-string<TNodeType>
     */
    public function getNodeType(): string
    {
        return CollectedDataNode::class;
    }

    /**
     * Processes the given node
     *
     * @phpstan-param TNodeType $node
     *
     * @return (string|RuleError)[] errors
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if ( ! $node instanceof CollectedDataNode) {
            return [];
        }

        $castCollector = collect($node->get(CastCollector::class))
            ->flatten()
            ->map(UnionType::fromString(...))
            ->reject(fn (UnionType $unionType) => $unionType->isMixed())
            ->values()
            ->all();

        $classCollector = collect($node->get(ConstructorCollector::class))
            ->flatten(1)
            ->map(Constructor::unserialize(...))
            ->keyBy('class')
            ->all();

        return collect($node->get(FromCollector::class))
            // Flatten from a list of calls pr. file, to just a list of calls.
            ->flatten(1)
            ->map(Call::unserialize(...))
            // Check each call for errors
            ->map(fn (Call $call) => $this->compareTypes($call, $castCollector, $classCollector[$call->target]))
            // Flatten from a list of errors pr. call, to just a list of errors.
            ->flatten()
            // To array, so PhpStan can serialize the data.
            ->all();
    }

    /**
     * Compares the types of the specific call, with the constructor which would be expected
     *
     * @param Call $call
     * @param list<UnionType> $casts
     * @param Constructor $constructor
     *
     * @throws ShouldNotHappenException
     *
     * @return array
     */
    private function compareTypes(Call $call, array $casts, Constructor $constructor): array
    {
        $errors = [];

        foreach ($call->arrayArguments as $arrayList) {
            foreach ($arrayList as $type) {
                // Ignore any additional data, since it does not matter
                if ( ! isset($constructor->properties[$type->key])) {
                    continue;
                }

                $error = $this->checkType($call, $type->key, $type->type, $casts, $constructor->properties[$type->key]->type);

                if ($error !== null) {
                    $errors[] = $error;
                }
            }
        }

        return $errors;
    }

    /**
     * Checks the specific type for a single key, with the expected types of that key.
     *
     * @param Call $call
     * @param string $key
     * @param UnionType $actualType
     * @param list<UnionType> $casts
     * @param UnionType $expectedType
     *
     * @throws ShouldNotHappenException
     *
     * @return RuleError|null
     */
    private function checkType(Call $call, string $key, UnionType $actualType, array $casts, UnionType $expectedType): ?RuleError
    {
        // Casters cannot cast nullable values, so quick test for type error
        // is simply to check if the expected type accepts null values or not.
        if ($actualType->isNullable() && $expectedType->isNotNullable()) {
            return $this->buildError($call, $key, $expectedType, $actualType);
        }

        // Otherwise, ignore cases where there exists a cast - since we cannot analyse them in dept.
        if ($this->expectedTypesMatchesExactlyCast($casts, $expectedType)) {
            return null;
        }

        // Run full type inspection and return any errors found.
        if ( ! TypeSystem::isSubtypeOf($actualType, $expectedType)) {
            return $this->buildError($call, $key, $expectedType, $actualType);
        }

        return null;
    }

    /**
     * Builds a RuleError instance
     *
     * @param Call $call
     * @param string $key
     * @param string $expectedType
     * @param string $actualType
     *
     * @throws ShouldNotHappenException
     *
     * @return RuleError
     */
    private function buildError(Call $call, string $key, string $expectedType, string $actualType): RuleError
    {
        return RuleErrorBuilder::message(self::getErrorMessage($key, $call->target, $expectedType, $actualType))
            ->line($call->method->line)
            ->file($call->method->file)
            ->tip('This is a custom CEGO rule, if you found a bug fix it in the cego/phpstan project')
            ->build();
    }

    /**
     * Returns the error message to give the developer on errors
     *
     * @param string $property
     * @param string $class
     * @param string $expectedType
     * @param string $actualType
     *
     * @return string
     */
    public static function getErrorMessage(string $property, string $class, string $expectedType, string $actualType): string
    {
        return sprintf('Argument $%s for %s::__construct() expects type [%s] but [%s] was given', $property, $class, $expectedType, $actualType);
    }

    /**
     * Returns true if a cast exists exactly for the expected types.
     *
     * @param list<UnionType> $casts
     * @param UnionType $expectedTypes
     *
     * @return bool
     */
    private function expectedTypesMatchesExactlyCast(array $casts, UnionType $expectedTypes): bool
    {
        $systemCasts = [UnionType::fromString(DataCollection::class)];

        foreach ([...$casts, ...$systemCasts] as $castType) {
            if (TypeSystem::isSubtypeOf($expectedTypes, $castType)) {
                return true;
            }
        }

        return false;
    }
}
