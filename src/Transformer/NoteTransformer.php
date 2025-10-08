<?php

namespace App\Transformer;

use App\Attribute\Transformer;
use Selective\Transformer\ArrayTransformer;

/**
 * Class responsible for transforming information related to notes.
 */
#[Transformer("note")]
class NoteTransformer extends TransformerBase implements TransformerInterface {

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
        $this->transformer->map('content', 'content', 'nullable_string|required')
            ->map('timeAdded', 'time-added', 'int')
            ->map('changes.status', 'changes.status-id', 'int')
            ->map('changes.priority', 'changes.priority-id', 'int')
            ->map('changes.category', 'changes.category-id', 'int')
            ->map('changes.assignee', 'changes.assignee-id', 'int')
            ->map('changes.milestone', 'changes.milestone-id', 'int')
            ->map('changes.subject', 'changes.subject', 'string')
            ->map('uploadTokens', 'upload-tokens.upload-token', 'array')
            ->map('private', 'private', 'int|required')
            ;
        return $this->transformer->toArray((array)$data);
    }

}
