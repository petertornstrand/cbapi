<?php

namespace App\Transformer;

use App\Attribute\Transformer;
use Selective\Transformer\ArrayTransformer;

/**
 * Class responsible for transforming data related to project status.
 */
#[Transformer("status")]
class StatusTransformer extends TransformerBase implements TransformerInterface {

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
        $this->transformer->map('id', 'id', 'int|required')
            ->map('name', 'name', 'string|required')
            ->map('backgroundColor', 'background-colour', 'string|required')
            ->map('order', 'order', 'int|required')
            ->map('treatAsClosed', 'treat-as-closed', 'bool|required');
        return $this->transformer->toArray((array)$data);
    }

}
