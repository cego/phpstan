<?php

namespace Cego\phpstan\TypeSystem;

use Stringable;

class IntersectionType implements Stringable
{
    /**
     * @var array<int, Type>
     */
    public array $types = [];

    /**
     * Constructor
     *
     * @param Type[] $types
     */
    public function __construct(array $types)
    {
        $this->types = $types;
    }

    /**
     * Constructor from array representation
     *
     * @param array<int, string> $intersectionType
     *
     * @return static
     */
    public static function fromRaw(array $intersectionType): self
    {
        return new self(collect($intersectionType)->mapInto(Type::class)->all());
    }

    /**
     * Returns the types composing the intersection type
     *
     * @return array<int, Type>
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    /**
     * Returns true if this is an intersection of a single type.
     * Meaning it is not actually an intersection type.
     *
     * @return bool
     */
    public function isIntersectionOfOne(): bool
    {
        return count($this->types) === 1;
    }

    /**
     * Returns the type in its string representation
     *
     * @return string
     */
    public function toString(): string
    {
        return $this->__toString();
    }

    /**
     * Allows a class to decide how it will react when it is treated like a string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return collect($this->types)
            ->map(fn (Type $type) => $type->toString())
            ->sort()
            ->implode('&');
    }

    /**
     * Returns true if the type is considered to accept "mixed"
     *
     * @return bool
     */
    public function isMixed(): bool
    {
        // An intersection type is mixed, if all the types are considered mixed.
        foreach ($this->types as $type) {
            if ( ! $type->isMixed()) {
                return false;
            }
        }

        return true;
    }
}
