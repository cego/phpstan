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
     * @return array<string, array<int, string>|null Collected data
     */
    public function processNode(Node $node, Scope $scope): ?array
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
                    $argData[$item->key->value] = get_debug_type($type->getValue());
                } else {
                    $argData[$item->key->value] = $scope->getType($item->value)->describe(VerbosityLevel::typeOnly());
                }
            }

            $types[] = $argData;
        }

        return [
            'types'  => $types,
            'target' => $this->getTargetClass($node, $scope),
            'method' => [
                'file' => $scope->getFile(),
                'line' => $node->getLine(),
            ],
        ];
    }

    private function isNotSpatieLaravelDataFromCall(StaticCall $node, Scope $scope): bool
    {
        if (strtolower($node->name->name) !== 'from') {
            return true;
        }

        return ! is_a($this->getTargetClass($node, $scope), Data::class, true);
    }

    private function getTargetClass(StaticCall $node, Scope $scope)
    {
        if ($node->class instanceof Node\Expr) {
            return $scope->getType($node->class)->getReferencedClasses()[0];
        }

        return $scope->resolveName($node->class);
    }
}
