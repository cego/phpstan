<?php

namespace Cego\phpstan\TypeSystem;

use Stringable;
use Illuminate\Support\Str;

class Type implements Stringable
{
    public readonly string $type;

    /**
     * Constructor
     *
     * @param string $type
     */
    public function __construct(string $type)
    {
        if (empty($type)) {
            $this->type = 'mixed';
        } else {
            $this->type = Str::of($type)->replaceMatches('/<.*>/', '')->toString();
        }
    }

    /**
     * Returns true if the type is not real, meaning a variable cannot have this type.
     *
     * @return bool
     */
    public function isNotReal(): bool
    {
        return in_array(strtolower($this->type), ['void', 'never'], true);
    }

    /**
     * Returns true if the type is null
     *
     * @return bool
     */
    public function isNull(): bool
    {
        return strtolower($this->type) === 'null';
    }

    /**
     * Returns true if the type is of mixed
     *
     * @return bool
     */
    public function isMixed(): bool
    {
        return strtolower($this->type) === 'mixed';
    }

    /**
     * Returns true if the type is a number
     *
     * @return bool
     */
    public function isNumber(): bool
    {
        return in_array(strtolower($this->type), ['int', 'float'], true);
    }

    /**
     * Returns true if the type is a float
     *
     * @return bool
     */
    public function isFloat(): bool
    {
        return strtolower($this->type) === 'float';
    }

    /**
     * Returns true if it is a class type
     *
     * @return bool
     */
    public function isClass(): bool
    {
        return class_exists($this->type);
    }

    /**
     * Returns true if the a interface type
     *
     * @return bool
     */
    public function isInterface(): bool
    {
        return interface_exists($this->type);
    }

    /**
     * Returns true if the given type exactly matches this type
     *
     * @param Type $type
     *
     * @return bool
     */
    public function equals(Type $type): bool
    {
        return strtolower($this->type) === strtolower($type->type);
    }

    /**
     * Returns true if this type is exactly the given type, or direct subset (for classes and interfaces)
     *
     * @param Type $type
     *
     * @return bool
     */
    public function isA(Type $type): bool
    {
        return strtolower($this->type) === strtolower($type->type)
            || is_a($this->type, $type->type, true);
    }

    /**
     * Returns true if the type is class or interface type
     *
     * @return bool
     */
    public function isClassOrInterface(): bool
    {
        return $this->isClass() || $this->isInterface();
    }

    /**
     * Returns the string in its string representation
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
        if ($this->isClassOrInterface()) {
            return ltrim($this->type, '\\');
        }

        // Handles when empty string == mixed
        if ($this->isMixed()) {
            return 'mixed';
        }

        return strtolower($this->type);
    }
}
