<?php

namespace App\Attribute;

use \Attribute;

/**
 * Represents a custom attribute used for decoration.
 */
#[Attribute]
final class Decorator {

    /**
     * Constructor.
     */
    public function __construct(public string $name) {}
}
