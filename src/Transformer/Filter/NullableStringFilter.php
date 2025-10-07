<?php

namespace App\Transformer\Filter;

/**
 * Filter.
 */
final class NullableStringFilter
{
    /**
     * Invoke.
     *
     * @param mixed $value The value
     *
     * @return string|null The value
     */
    public function __invoke($value)
    {
        if (
            $value instanceof \SimpleXMLElement
            && empty((string)$value)
            && (string)$value->attributes()?->nil === 'true'
        ) {
            return null;
        }
        return $value === '' ? null : (string)$value;
    }
}
