<?php

namespace Cego\phpstan\SpatieLaravelData\Collectors;

use PhpParser\Node;
use RuntimeException;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PHPStan\Analyser\Scope;
use Spatie\LaravelData\Data;
use PhpParser\Node\Identifier;
use PhpParser\Node\ComplexType;
use PHPStan\Collectors\Collector;
use PHPStan\Node\InClassMethodNode;
use Cego\phpstan\TypeSystem\UnionType;
use PHPStan\Reflection\ClassReflection;
use Cego\phpstan\SpatieLaravelData\Data\Constructor;
use Cego\phpstan\SpatieLaravelData\Data\KeyTypePair;

/**
 * @implements Collector<InClassMethodNode, array<string, array<string, array<int, string>>>
 */
class ConstructorCollector implements Collector
{
    /**
     * Returns the node type, this collector operates on
     *
     * @phpstan-return class-string<InClassMethodNode>
     */
    public function getNodeType(): string
    {
        return InClassMethodNode::class;
    }

    /**
     * Process the nodes and stores value in the collector instance
     *
     * @phpstan-param InClassMethodNode $node
     *
     * @return string|null Collected data
     */
    public function processNode(Node $node, Scope $scope): ?string
    {
        if ( ! $node instanceof InClassMethodNode) {
            return null;
        }

        if ($this->isNotSpatieLaravelDataConstructor($node)) {
            return null;
        }

        return serialize(new Constructor(
            $node->getMethodReflection()->getDeclaringClass()->getName(),
            collect($node->getOriginalNode()->getParams())->map($this->getParameterTypes(...))->all()
        ));
    }

    /**
     * Returns a key-value mapping of the parameter name and its allowed types
     *
     * @param Param $parameter
     *
     * @return KeyTypePair
     */
    private function getParameterTypes(Param $parameter): KeyTypePair
    {
        return new KeyTypePair(
            $this->getParameterName($parameter),
            UnionType::fromRaw($this->parseType($parameter->type)),
        );
    }

    /**
     * @param null|Identifier|Name|ComplexType $type
     *
     * @return array<int, array<int, string>>
     */
    private function parseType($type): array
    {
        // If no type is defined, then return mixed.
        if ($type === null) {
            return [['mixed']];
        }

        // Simple type (int, string, bool)
        if ($type instanceof Identifier) {
            return [[$type->name]];
        }

        // Class types
        if ($type instanceof Name) {
            // We do not support special type checking (self, parent, static)
            // since we are unlikely to use this feature,
            // and implementing it is currently not straight forward.
            if ($type->isSpecialClassName()) {
                return [['mixed']];
            }

            return [[$type->toCodeString()]];
        }

        // Complex types
        if ($type instanceof Node\ComplexType) {
            if ($type instanceof Node\NullableType) {
                return [
                    ...$this->parseType($type->type),
                    ['null'],
                ];
            }

            if ($type instanceof Node\UnionType) {
                return collect($type->types)
                    ->map(fn ($unionType) => $this->parseType($unionType))
                    ->flatten(1)
                    ->all();
            }

            if ($type instanceof Node\IntersectionType) {
                return [
                    collect($type->types)
                        ->map(fn ($intersectionType) => $this->parseType($intersectionType))
                        ->flatten(2)
                        ->all(),
                ];
            }
        }

        return [['mixed']];
    }

    /**
     * Returns the name of the given parameter
     *
     * @param Param $parameter
     *
     * @return string
     */
    private function getParameterName(Param $parameter): string
    {
        if ( ! is_string($parameter->var->name)) {
            throw new RuntimeException('A constructor property name cannot be an expression');
        }

        return $parameter->var->name;
    }

    /**
     * Returns true if the given node is not a laravel data constructor
     *
     * @param InClassMethodNode $node
     *
     * @return bool
     */
    private function isNotSpatieLaravelDataConstructor(InClassMethodNode $node): bool
    {
        return $this->isNotConstructor($node)
            || $this->isNotSpatieLaravelDataClass($node->getMethodReflection()->getDeclaringClass());
    }

    /**
     * Returns true if the given node is not a constructor class
     *
     * @param InClassMethodNode $node
     *
     * @return bool
     */
    private function isNotConstructor(InClassMethodNode $node): bool
    {
        return $node->getMethodReflection()->getName() !== '__construct';
    }

    /**
     * Returns true if the given class is not a laravel data class
     *
     * @param ClassReflection $class
     *
     * @return bool
     */
    private function isNotSpatieLaravelDataClass(ClassReflection $class): bool
    {
        return ! in_array(Data::class, $class->getParentClassesNames(), true);
    }
}
