<?php

namespace Cego\phpstan\SpatieLaravelData\Data;

use Cego\phpstan\SpatieLaravelData\Traits\UnserializesSelf;

class Method
{
    use UnserializesSelf;

    /**
     * Constructor
     *
     * @param string $file
     * @param int $line
     */
    public function __construct(
        public readonly string $file,
        public readonly int $line,
    ) {
    }

    /**
     * Returns array containing all the necessary state of the object.
     */
    public function __serialize(): array
    {
        return [
            'file' => $this->file,
            'line' => $this->line,
        ];
    }

    /**
     * Restores the object state from the given data array.
     *
     * @param array $data
     */
    public function __unserialize(array $data): void
    {
        $this->file = $data['file'];
        $this->line = $data['line'];
    }
}
