<?php

namespace Cego\phpstan\SpatieLaravelData\Collectors;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use Spatie\LaravelData\Data;
use PhpParser\Node\Expr\Array_;
use PHPStan\Type\VerbosityLevel;
use PHPStan\Collectors\Collector;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Type\ConstantScalarType;
use Cego\phpstan\TypeSystem\UnionType;
use Cego\phpstan\SpatieLaravelData\Data\Call;
use Cego\phpstan\SpatieLaravelData\Data\Method;
use Cego\phpstan\SpatieLaravelData\Data\KeyTypePair;

/**
 * @implements Collector<StaticCall, array<string, array<int, string>>
 */
class FromCollector implements Collector
{
    /**
     * Returns the node type, this collector operates on
     *
     * @phpstan-return class-string<InClassMethodNode>
     */
    public function getNodeType(): string
    {
        return StaticCall::class;
    }

    /**
     * Process the nodes and stores value in the collector instance
     *
     * @phpstan-param StaticCall $node
     *
     * @return string|null Collected data
     */
    public function processNode(Node $node, Scope $scope): ?string
    {
        if ( ! $node instanceof StaticCall) {
            return null;
        }

        if ($this->isNotSpatieLaravelDataFromCall($node, $scope)) {
            return null;
        }

        $types = [];

        foreach ($node->args as $arg) {
            if ($arg instanceof Node\VariadicPlaceholder) {
                continue;
            }

            if ( ! $arg->value instanceof Array_) {
                continue;
            }

            $argData = [];

            foreach ($arg->value->items as $item) {
                if ( ! $item->key instanceof Node\Scalar\String_) {
                    continue;
                }

                $type = $scope->getType($item->value);

                if ($type instanceof ConstantScalarType) {
                    $argData[] = new KeyTypePair($item->key->value, UnionType::fromString(get_debug_type($type->getValue())));
                } else {
                    $argData[] = new KeyTypePair($item->key->value, UnionType::fromString($scope->getType($item->value)->describe(VerbosityLevel::typeOnly())));
                }
            }

            $types[] = $argData;
        }

        return serialize(new Call(
            $this->getTargetClass($node, $scope),
            $types,
            new Method(
                $scope->getFile(),
                $node->getLine(),
            )
        ));
    }

    /**
     * Returns true if the given node is not a laravel data class static ::From call
     *
     * @param StaticCall $node
     * @param Scope $scope
     *
     * @return bool
     */
    private function isNotSpatieLaravelDataFromCall(StaticCall $node, Scope $scope): bool
    {
        if (strtolower($node->name->name) !== 'from') {
            return true;
        }

        return ! is_a($this->getTargetClass($node, $scope), Data::class, true);
    }

    /**
     * Returns the target / result class of the given static call
     *
     * @param StaticCall $node
     * @param Scope $scope
     *
     * @return string
     */
    private function getTargetClass(StaticCall $node, Scope $scope)
    {
        if ($node->class instanceof Node\Expr) {
            return $scope->getType($node->class)->getReferencedClasses()[0];
        }

        return $scope->resolveName($node->class);
    }
}
