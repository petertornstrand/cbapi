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
        $this->transformer->set('attachments', [])->rule()->array();
        $this->transformer->map('id', 'id', 'int|required')
            ->map('userId', 'user-id', 'int|required')
            ->map('created', 'created-at', 'string|required')
            ->map('updated', 'updated-at', 'string')
            ->map('content', 'content', 'nullable_string|required')
            ->map('updates', 'updates', 'string|required')
            ->map('companyId', 'company-id', 'nullable_int|required')
            ->map('timeAdded', 'time-added', 'int') // TODO: Does this really exist?
            ->map('attachments.attachment.id', 'attachments.attachment.id', 'int')
            ->map('attachments.attachment.guid', 'attachments.attachment.identifier', 'string')
            ->map('attachments.attachment.filename', 'attachments.attachment.file-name', 'string')
            ->map('attachments.attachment.contentType', 'attachments.attachment.content-type', 'string')
            ->map('attachments.attachment.fileSize', 'attachments.attachment.file-size', 'int')
            ->map('attachments.attachment.url', 'attachments.attachment.url', 'string')
            ->map('private', 'private', 'int');
        return $this->transformer->toArray((array)$data);
    }

}
