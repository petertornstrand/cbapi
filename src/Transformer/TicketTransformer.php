<?php

namespace App\Transformer;

use App\Attribute\Transformer;
use Selective\Transformer\ArrayTransformer;

/**
 * Class responsible for transforming information related to tickets.
 * Extends the base transformer functionality to map various ticket-related
 * fields from an XML structure into a structured array format.
 */
#[Transformer("ticket")]
class TicketTransformer extends TransformerBase implements TransformerInterface {

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
        $this->transformer->map('id', 'ticket-id', 'int')
            ->map('summary', 'summary', 'string')
            ->map('reporter.id', 'reporter-id', 'int')
            ->map('reporter.username', 'reporter', 'string')
            ->map('assignee.id', 'assignee-id', 'int')
            ->map('assignee.username', 'assignee', 'string')
            ->map('category.id', 'category.id', 'int')
            ->map('category.name', 'category.name', 'string')
            ->map('priority.id', 'priority.id', 'int')
            ->map('priority.name', 'priority.name', 'string')
            ->map('priority.color', 'priority.colour', 'string')
            ->map('priority.default', 'priority.default', 'bool')
            ->map('priority.order', 'priority.position', 'int')
            ->map('status.id', 'status.id', 'int')
            ->map('status.name', 'status.name', 'string')
            ->map('status.color', 'status.colour', 'string')
            ->map('status.order', 'status.order', 'int')
            ->map('status.treatAsClosed', 'status.treat-as-closed', 'bool')
            ->map('type.id', 'type.id', 'int')
            ->map('type.name', 'type.name', 'string')
            ->map('type.icon', 'type.icon', 'string')
            ->map('milestone.id', 'milestone.id', 'int')
            ->map('milestone.parentId', 'milestone.parent-id', 'nullable_int|required')
            ->map('milestone.guid', 'milestone.identifier', 'string')
            ->map('milestone.name', 'milestone.name', 'string')
            ->map('milestone.startDate', 'milestone.start-at', 'string')
            ->map('milestone.endDate', 'milestone.deadline', 'string')
            ->map('milestone.description', 'milestone.description', 'string')
            ->map('milestone.responsibleUserId', 'milestone.responsible-user-id', 'int')
            ->map('milestone.estimatedTime', 'milestone.estimated-time', 'int')
            ->map('milestone.status', 'milestone.status', 'string')
            ->map('startDate', 'start-on', 'nullable_string|required')
            ->map('endDate', 'deadline', 'nullable_string|required')
            ->map('tags', 'tags', 'nullable_string|required')
            ->map('updated', 'updated-at', 'string')
            ->map('created', 'created-at', 'string')
            ->map('estimatedTime', 'estimated-time', 'nullable_int|required')
            ->map('spentTime', 'total-time-spent', 'int')
            ->map('projectId', 'project-id', 'int');
        return $this->transformer->toArray((array)$data);
    }

}
