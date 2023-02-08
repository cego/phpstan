<?php

namespace Cego\phpstan\SpatieLaravelData\Data;

use Cego\phpstan\SpatieLaravelData\Traits\UnserializesSelf;

class Call
{
    use UnserializesSelf;

    /**
     * Constructor
     *
     * @param string $target
     * @param array<int, array<int, KeyTypePair>> $arrayArguments
     * @param Method $method
     */
    public function __construct(
        public readonly string $target,
        public readonly array  $arrayArguments,
        public readonly Method $method,
    ) {
    }

    /**
     * Returns array containing all the necessary state of the object.
     */
    public function __serialize(): array
    {
        return [
            'target'         => $this->target,
            'method'         => serialize($this->method),
            'arrayArguments' => serialize($this->arrayArguments),
        ];
    }

    /**
     * Restores the object state from the given data array.
     *
     * @param array $data
     */
    public function __unserialize(array $data): void
    {
        $this->target = $data['target'];
        $this->method = Method::unserialize($data['method']);
        $this->arrayArguments = unserialize($data['arrayArguments'], ['allowed_classes' => [KeyTypePair::class]]);
    }
}
