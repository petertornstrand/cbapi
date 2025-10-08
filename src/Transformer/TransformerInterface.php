<?php

namespace App\Transformer;

/**
 * Interface for transformers.
 */
interface TransformerInterface {

    /**
     * Transforms the given SimpleXMLElement object into an array.
     *
     * @param array $data
     * @return array
     */
    public function transform(array $data): array;
}
