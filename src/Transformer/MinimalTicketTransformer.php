<?php

namespace App\Transformer;

use App\Attribute\Transformer;
use Selective\Transformer\ArrayTransformer;

/**
 * Class responsible for transforming information related to tickets. This
 * transformer is a variant of the ticket transformer used to extract minimal
 * ticket information for display.
 */
#[Transformer("ticket_min")]
class MinimalTicketTransformer extends TransformerBase implements TransformerInterface {

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
        $this->transformer->map('id', 'ticket-id', 'int|required')
            ->map('subject', 'summary', 'string|required')
            ->map('created', 'created-at', 'date|required')
            ->map('assigneeId', 'assignee-id', 'nullable_int|required')
            ->map('statusName', 'status.name', 'string|required')
            ->map('statusColor', 'status.colour', 'string|required')
            ->map('typeName', 'type.name', 'string|required')
            ->map('typeIcon', 'type.icon', 'string|required');
        return $this->transformer->toArray((array)$data);
    }

}
