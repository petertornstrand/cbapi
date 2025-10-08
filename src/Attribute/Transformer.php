<?php

namespace App\Attribute;

use \Attribute;

/**
 * Represents a custom attribute used for transformation.
 */
#[Attribute]
final class Transformer {

    /**
     * Constructor.
     */
    public function __construct(public string $name) {}
}
