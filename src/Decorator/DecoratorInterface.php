<?php

namespace App\Decorator;

/**
 * Interface for decorators.
 */
interface DecoratorInterface {

    /**
     * Decorates the given data with additional information.
     *
     * @param array $data
     * @return array
     */
    public function decorate(array &$data): void;
}
