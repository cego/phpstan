<?php

namespace Cego\phpstan\SpatieLaravelData\Traits;

trait UnserializesSelf
{
    public static function unserialize(string $representation): self
    {
        return unserialize($representation, ['allowed_classes' => [__CLASS__]]);
    }
}
