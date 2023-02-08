<?php

namespace Cego\phpstan\SpatieLaravelData\Data;

use Cego\phpstan\SpatieLaravelData\Traits\UnserializesSelf;

class Constructor
{
    use UnserializesSelf;

    public readonly array $properties;

    /**
     * Constructor
     *
     * @param string $class
     * @param array<string, KeyTypePair> $properties
     */
    public function __construct(
        public readonly string $class,
        array $properties
    ) {
        $this->properties = collect($properties)->keyBy('key')->all();
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
            'class'      => $this->class,
            'properties' => serialize($this->properties),
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
        $this->class = $data['class'];
        $this->properties = unserialize($data['properties'], ['allowed_classes' => [KeyTypePair::class]]);
    }
}
