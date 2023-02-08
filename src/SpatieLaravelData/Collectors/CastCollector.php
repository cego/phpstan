<?php

namespace Cego\phpstan\SpatieLaravelData\Collectors;

use PhpParser\Node;
use Illuminate\Support\Str;
use PHPStan\Analyser\Scope;
use PHPStan\Type\VerbosityLevel;
use PHPStan\Collectors\Collector;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Casts\Cast;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Node\InClassMethodNode;
use PHPStan\ShouldNotHappenException;
use Cego\phpstan\TypeSystem\UnionType;
use Spatie\LaravelData\Casts\Uncastable;
use PHPStan\Reflection\ParametersAcceptorSelector;

/**
 * @implements Collector<InClassMethodNode, array<string, array<int, string>>
 */
class CastCollector implements Collector
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
     * @phpstan-param StaticCall $node
     *
     * @throws ShouldNotHappenException
     *
     * @return string|null Collected data
     */
    public function processNode(Node $node, Scope $scope): ?string
    {
        // Skip wrong nodes
        if ( ! $node instanceof InClassMethodNode) {
            return null;
        }

        // Skip wrong methods
        if ($this->isNotCastMethod($node)) {
            return null;
        }

        $variant = ParametersAcceptorSelector::selectSingle($node->getMethodReflection()->getVariants());
        $returnType = $variant->getReturnType();

        return Str::of($returnType->describe(VerbosityLevel::typeOnly()))
            // Get individual union types
            ->explode('|')
            // Get individual intersection types
            ->map(fn (string $type) => Str::of($type)->explode('&'))
            // For each intersection type (which might be an intersection of 1 item)
            // Only keep cast information for classes / interfaces
            ->map(function (Collection $intersectionTypes) {
                $classTypes = $intersectionTypes
                    // We only care about classes / interfaces
                    ->filter(fn (string $type) => class_exists($type) || interface_exists($type))
                    // We do not care for the uncastable class
                    ->reject(fn (string $type) => is_a($type, Uncastable::class, true));

                // We only support intersection types of explicit classes / interfaces.
                if ($intersectionTypes->count() !== $classTypes->count()) {
                    return [];
                }

                return $classTypes->all();
            })
            // Remove any intersection types we have deemed unfit
            ->reject(fn (array $collection) => empty($collection))
            ->pipe(UnionType::fromRaw(...))
            ->toString();
    }

    /**
     * Returns true if the given node is not the cast method of a Cast class
     *
     * @param InClassMethodNode $node
     *
     * @return bool
     */
    private function isNotCastMethod(InClassMethodNode $node): bool
    {
        return $node->getMethodReflection()->getName() !== 'cast'
            || ! $node->getMethodReflection()->getDeclaringClass()->implementsInterface(Cast::class);
    }
}
