<?php

namespace App\Transformer;

use App\Attribute\Transformer;
use Selective\Transformer\ArrayTransformer;

/**
 * Class responsible for transforming data related to groups.
 */
#[Transformer("group")]
class GroupTransformer extends TransformerBase implements TransformerInterface {

    /**
     * @inheritDoc
     */
    public function __construct(
        protected ArrayTransformer $transformer
    ) {
        parent::__construct($transformer);
    }

    /**
     * @inheritDoc
     */
    public function transform(array $data): array {
        $this->transformer->map('id', 'id', 'int')
            ->map('label', 'label', 'string');
        return $this->transformer->toArray((array)$data);
    }

}
