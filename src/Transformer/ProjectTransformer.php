<?php

namespace App\Transformer;

use App\Attribute\Transformer;
use Selective\Transformer\ArrayTransformer;

/**
 * Class responsible for transforming data related to project types.
 */
#[Transformer("project")]
class ProjectTransformer extends TransformerBase implements TransformerInterface {

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
        $this->transformer->map('id', 'group-id', 'int|required')
            ->map('icon', 'icon', 'int')
            ->map('name', 'name', 'string|required')
            ->map('accountName', 'account-name', 'string')
            ->map('overview', 'overview', 'string')
            ->map('permalink', 'permalink', 'string|required')
            ->map('startPage', 'start-page', 'string')
            ->map('status', 'status', 'string')
            ->map('totalTickets', 'total-tickets', 'int')
            ->map('openTickets', 'open-tickets', 'int')
            ->map('closedTickets', 'closed-tickets', 'int');
        return $this->transformer->toArray((array)$data);
    }

}
