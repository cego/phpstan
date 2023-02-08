<?php

namespace Cego\phpstan\SpatieLaravelData\Data;

use Cego\phpstan\TypeSystem\UnionType;
use Cego\phpstan\SpatieLaravelData\Traits\UnserializesSelf;

class KeyTypePair
{
    use UnserializesSelf;

    /**
     * Constructor
     *
     * @param string $key
     * @param UnionType $type
     */
    public function __construct(
        public readonly string    $key,
        public readonly UnionType $type,
    ) {
    }

    /**
     * Returns array containing all the necessary state of the object.
     */
    public function __serialize(): array
    {
        return [
            'key'  => $this->key,
            'type' => serialize($this->type),
        ];
    }

    /**
     * Restores the object state from the given data array.
     *
     * @param array $data
     */
    public function __unserialize(array $data): void
    {
        $this->key = $data['key'];
        $this->type = UnionType::unserialize($data['type']);
    }
}
