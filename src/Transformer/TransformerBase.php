<?php

namespace App\Transformer;

use App\Transformer\Filter\NullableIntegerFilter;
use App\Transformer\Filter\NullableStringFilter;
use Selective\Transformer\ArrayTransformer;

/**
 * Base class for data transformation processes.
 *
 * This abstract class provides the necessary structure for implementing
 * data transformers. It initializes with a provided ArrayTransformer and
 * pre-registers custom filters for data processing.
 */
abstract class TransformerBase implements TransformerInterface
{

    /**
     * Constructor.
     */
    public function __construct(
        protected ArrayTransformer $transformer
    )
    {
        $this->transformer->registerFilter('nullable_string', new NullableStringFilter());
        $this->transformer->registerFilter('nullable_int', new NullableIntegerFilter());
    }

    /**
     * @inheritDoc
     */
    abstract function transform(array $data): array;

}
