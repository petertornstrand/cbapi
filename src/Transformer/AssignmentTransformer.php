<?php

namespace App\Transformer;

use App\Attribute\Transformer;
use Selective\Transformer\ArrayTransformer;

/**
 * Class responsible for transforming data related to project assignments.
 */
#[Transformer("assignment")]
class AssignmentTransformer extends TransformerBase implements TransformerInterface{

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
            ->map('company', 'company', 'nullable_string|required')
            ->map('firstName', 'first-name', 'string|required')
            ->map('lastName', 'last-name', 'string|required')
            ->map('username', 'username', 'string|required')
            ->map('email', 'email-address', 'string|required');
        return $this->transformer->toArray((array)$data);
    }

}
