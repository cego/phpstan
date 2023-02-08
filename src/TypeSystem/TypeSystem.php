<?php

namespace Cego\phpstan\TypeSystem;

class TypeSystem
{
    /**
     * Returns true if the given type is a subset of the given parent type
     *
     * @param UnionType $type
     * @param UnionType $parentType
     *
     * @return bool
     */
    public static function isSubtypeOf(UnionType $type, UnionType $parentType): bool
    {
        // For a union type A to be a subset of another union type B,
        // then all type options of A must be a subset of at least one of the
        // type options of union type B.
        foreach ($type->getIntersectionTypes() as $intersectionType) {
            if ( ! self::isIntersectionTypeSubsetOfUnionType($intersectionType, $parentType)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns true if the given intersection type is a subset of the given parent type
     *
     * @param IntersectionType $intersectionType
     * @param UnionType $parentType
     *
     * @return bool
     */
    private static function isIntersectionTypeSubsetOfUnionType(IntersectionType $intersectionType, UnionType $parentType): bool
    {
        // For an intersection type to be a subset of a union type, then at least one of the underlying types
        // for the intersection type has to be a subset of the type options for the union type
        foreach ($intersectionType->getTypes() as $type) {
            if (self::isTypeSubsetOfUnionType($type, $parentType)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns true if the given type, is a subset of the given union type
     *
     * @param Type $type
     * @param UnionType $parentUnionType
     *
     * @return bool
     */
    private static function isTypeSubsetOfUnionType(Type $type, UnionType $parentUnionType): bool
    {
        // For a specific type to be a subset of a union type, then the type
        // has to be a subset of only one of the types of the underlying intersection types.
        foreach ($parentUnionType->getIntersectionTypes() as $parentIntersectionType) {
            foreach ($parentIntersectionType->getTypes() as $parentType) {
                if (self::isTypeSubsetOfType($type, $parentType)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Returns true if the given type is a subset of the given parent type
     *
     * @param Type $type
     * @param Type $parentType
     *
     * @return bool
     */
    private static function isTypeSubsetOfType(Type $type, Type $parentType): bool
    {
        // A non-real type cannot exist as a variable, and therefor is never a subtype.
        if ($type->isNotReal()) {
            return false;
        }

        // Everything is a subset of mixed
        if ($parentType->isMixed()) {
            return true;
        }

        // All numbers are a subset of float
        if ($type->isNumber() && $parentType->isFloat()) {
            return true;
        }

        // If a type equals or is a instance of the parent type, then its a subset.
        if ($type->isA($parentType)) {
            return true;
        }

        // Otherwise it is not.
        return false;
    }
}
