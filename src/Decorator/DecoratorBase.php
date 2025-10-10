<?php

namespace App\Decorator;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Base class for data decoration processes.
 */
abstract class DecoratorBase implements DecoratorInterface
{

    protected array $data = [];

    /**
     * Constructor for DecoratorBase.
     */
    public function __construct(
        protected string $name,
        #[Autowire('%kernel.project_dir%')] protected string $directory,
        #[Autowire('%kernel.environment%')] protected string $environment,
        protected FileSystem $fileSystem
    ) {
        $this->initDecoration($this->name);
    }

    /**
     * @inheritDoc
     */
    public function decorate(array &$data): void {
        $template = $this->getDecorationTemplate();
        $decoration = $this->getDecoration($data['id']);
        $decoration = array_merge($template, $decoration);
        if ($decoration) {
            $data += $decoration;
        }
    }

    /**
     * Initializes the decoration data.
     */
    protected function initDecoration(string $decorator): void {
        $data = [];
        $path = $this->directory . "/var/storage/{$decorator}.decorator.json";
        if ($this->fileSystem->exists($path)) {
            $data = json_decode($this->fileSystem->readFile($path), true);
        }
        $this->data = $data;
    }

    /**
     * Gets the decoration data for the given ID.
     */
    protected function getDecoration(mixed $id): array {
        $items = array_filter($this->data, fn($item) => $item['_ID_'] == $id);
        if (empty($items)) return [];
        $item = current($items);
        unset($item['_ID_']);
        return $item;
    }

    /**
     * Gets the decoration data for the given ID.
     */
    protected function getDecorationTemplate(): ?array {
        $template = $this->getDecoration('template');
        foreach ($template as $key => $value) {
            if (str_starts_with($value, '_')) {
                unset($template[$key]);
            }
        }
        return $template;
    }

}
