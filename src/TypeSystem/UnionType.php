<?php

namespace Cego\phpstan\TypeSystem;

use Illuminate\Support\Str;
use Illuminate\Contracts\Support\Arrayable;
use Cego\phpstan\SpatieLaravelData\Traits\UnserializesSelf;

class UnionType implements \Stringable
{
    use UnserializesSelf;

    /**
     * @var array<int, IntersectionType>
     */
    private array $intersectionTypes;

    /**
     * @param IntersectionType[] $intersectionTypes
     */
    public function __construct(array $intersectionTypes)
    {
        $this->intersectionTypes = $intersectionTypes;
    }

    public static function fromRaw(array|Arrayable $unionType): self
    {
        if ($unionType instanceof Arrayable) {
            $unionType = $unionType->toArray();
        }

        return new self(collect($unionType)->map(IntersectionType::fromRaw(...))->all());
    }

    public static function fromString(string $type): self
    {
        return self::fromRaw(
            Str::of($type)
                ->explode('|')
                ->map(fn (string $type) => explode('&', $type))
                ->all()
        );
    }

    public function isMixed(): bool
    {
        // A union type is mixed, if just one of the types are considered mixed
        foreach ($this->intersectionTypes as $type) {
            if ($type->isMixed()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<int, IntersectionType>
     */
    public function getIntersectionTypes(): array
    {
        return $this->intersectionTypes;
    }

    public function isUnionOfOne(): bool
    {
        return count($this->intersectionTypes) === 1;
    }

    public function toString(): string
    {
        return $this->__toString();
    }

    /**
     * Magic method {@see https://www.php.net/manual/en/language.oop5.magic.php#object.tostring}
     * allows a class to decide how it will react when it is treated like a string.
     *
     * @return string Returns string representation of the object that
     * implements this interface (and/or "__toString" magic method).
     */
    public function __toString(): string
    {
        $type = collect($this->intersectionTypes)
            ->map(function (IntersectionType $intersectionType) {
                if ($intersectionType->isIntersectionOfOne()) {
                    return $intersectionType->toString();
                }

                return sprintf('(%s)', $intersectionType->toString());
            })
            ->sort()
            ->implode('|');

        // No need to add parentheses from intersection, unless there are more types.
        if ($this->isUnionOfOne()) {
            return trim($type, '()');
        }

        return $type;
    }

    /**
     * Returns array containing all the necessary state of the object.
     *
     * @since 7.4
     * @link https://wiki.php.net/rfc/custom_object_serialization
     */
    public function __serialize(): array
    {
        return [
            'type' => $this->toString(),
        ];
    }

    /**
     * Restores the object state from the given data array.
     *
     * @param array $data
     *
     * @since 7.4
     * @link https://wiki.php.net/rfc/custom_object_serialization
     */
    public function __unserialize(array $data): void
    {
        $this->intersectionTypes = self::fromString($data['type'])->intersectionTypes;
    }
}
