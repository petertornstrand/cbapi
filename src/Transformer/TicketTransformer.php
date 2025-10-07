<?php

namespace App\Transformer;

use Selective\Transformer\ArrayTransformer;

/**
 * Class responsible for transforming information related to tickets.
 * Extends the base transformer functionality to map various ticket-related
 * fields from an XML structure into a structured array format.
 */
class TicketTransformer extends TransformerBase {

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
    public function transform(mixed $data): array {
        $this->transformer->map('id', 'ticket.ticket-id', 'int')
            ->map('summary', 'ticket.summary', 'string')
            ->map('reporter.id', 'ticket.reporter-id', 'int')
            ->map('reporter.username', 'ticket.reporter', 'string')
            ->map('assignee.id', 'ticket.assignee-id', 'int')
            ->map('assignee.username', 'ticket.assignee', 'string')
            ->map('category.id', 'ticket.category.id', 'int')
            ->map('category.name', 'ticket.category.name', 'string')
            ->map('priority.id', 'ticket.priority.id', 'int')
            ->map('priority.name', 'ticket.priority.name', 'string')
            ->map('priority.color', 'ticket.priority.colour', 'string')
            ->map('priority.default', 'ticket.priority.default', 'bool')
            ->map('priority.order', 'ticket.priority.position', 'int')
            ->map('status.id', 'ticket.status.id', 'int')
            ->map('status.name', 'ticket.status.name', 'string')
            ->map('status.color', 'ticket.status.colour', 'string')
            ->map('status.order', 'ticket.status.order', 'int')
            ->map('status.treatAsClosed', 'ticket.status.treat-as-closed', 'bool')
            ->map('type.id', 'ticket.type.id', 'int')
            ->map('type.name', 'ticket.type.name', 'string')
            ->map('type.icon', 'ticket.type.icon', 'string')
            ->map('milestone.id', 'ticket.milestone.id', 'int')
            ->map('milestone.parentId', 'ticket.milestone.parent-id', 'nullable_int|required')
            ->map('milestone.guid', 'ticket.milestone.identifier', 'string')
            ->map('milestone.name', 'ticket.milestone.name', 'string')
            ->map('milestone.startDate', 'ticket.milestone.start-at', 'string')
            ->map('milestone.endDate', 'ticket.milestone.deadline', 'string')
            ->map('milestone.description', 'ticket.milestone.description', 'string')
            ->map('milestone.responsibleUserId', 'ticket.milestone.responsible-user-id', 'int')
            ->map('milestone.estimatedTime', 'ticket.milestone.estimated-time', 'int')
            ->map('milestone.status', 'ticket.milestone.status', 'string')
            ->map('startDate', 'ticket.start-on', 'nullable_string|required')
            ->map('endDate', 'ticket.deadline', 'nullable_string|required')
            ->map('tags', 'ticket.tags', 'nullable_string|required')
            ->map('updated', 'ticket.updated-at', 'string')
            ->map('created', 'ticket.created-at', 'string')
            ->map('estimatedTime', 'ticket.estimated-time', 'nullable_int|required')
            ->map('spentTime', 'ticket.total-time-spent', 'int')
            ->map('projectId', 'ticket.project-id', 'int');
        return $this->transformer->toArray((array)$data);
    }

}
