<?php

namespace App;

use App\Attribute\Decorator;
use App\Decorator\DecoratorInterface;
use Spatie\StructureDiscoverer\Cache\FileDiscoverCacheDriver;
use Spatie\StructureDiscoverer\Discover;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Factory class responsible for creating decorator instances.
 */
class DecoratorFactory {

    protected array $decorators = [];

    /**
     * Constructor.
     */
    public function __construct(
        #[Autowire('%kernel.project_dir%')] protected string $directory,
        #[Autowire('%kernel.environment%')] protected string $environment,
        protected FileSystem $fileSystem
    ) {
        $this->discover();
    }

    /**
     * Discovers and retrieves a collection of decorators within the specified
     * directory that possess a particular attribute.
     *
     * @throws \ReflectionException
     * @throws \Spatie\StructureDiscoverer\Exceptions\NoCacheConfigured
     */
    protected function discover(): void {
        $classDirectory = $this->directory . '/src/Decorator';
        $cacheDirectory = $this->directory . '/var/cache/' . $this->environment;
        $decorators = Discover::in($classDirectory)->withCache(
            'decorator_discovery',
            new FileDiscoverCacheDriver($cacheDirectory)
        )->classes()->withAttribute(Decorator::class)->get();
        foreach ($decorators as $decorator) {
            $reflection = new \ReflectionClass($decorator);
            $attribs = $reflection->getAttributes();
            foreach ($attribs as $attrib) {
                $instance = $attrib->newInstance();
                $this->decorators[$instance->name] = $decorator;
            }
        }
    }

    /**
     * Creates and returns a decorator instance based on the provided
     * decorator name.
     *
     * @throws \InvalidArgumentException If the decorator is not found.
     */
    public function create(string $decorator): DecoratorInterface {
        if (!isset($this->decorators[$decorator])) {
            throw new \InvalidArgumentException(sprintf('Decorator "%s" not found.', $decorator));
        }
        $class = $this->decorators[$decorator];
        if ($class instanceof DecoratorInterface) {
            return $class;
        }
        return new $class($decorator, $this->directory, $this->environment, $this->fileSystem);
    }

}
