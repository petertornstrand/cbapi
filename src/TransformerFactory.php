<?php

namespace App;

use App\Attribute\Transformer;
use App\Transformer\TransformerInterface;
use Selective\Transformer\ArrayTransformer;
use Spatie\StructureDiscoverer\Cache\FileDiscoverCacheDriver;
use Spatie\StructureDiscoverer\Discover;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class TransformerFactory {

    protected array $transformers = [];

    /**
     * Constructor.
     */
    public function __construct(
        #[Autowire('%kernel.project_dir%')] protected string $directory,
        #[Autowire('%kernel.environment%')] protected string $environment,
    ) {
        $this->discover();
    }

    /**
     * Discovers and retrieves a collection of transformers within the specified
     * directory that possess a particular attribute.
     *
     * @throws \ReflectionException
     * @throws \Spatie\StructureDiscoverer\Exceptions\NoCacheConfigured
     */
    protected function discover(): void {
        $classDirectory = $this->directory . '/src/Transformer';
        $cacheDirectory = $this->directory . '/var/cache/' . $this->environment;
        $transformers = Discover::in($classDirectory)->withCache(
            'transformer-discovery',
            new FileDiscoverCacheDriver($cacheDirectory)
        )->classes()->withAttribute(Transformer::class)->get();
        foreach ($transformers as $transformer) {
            $reflection = new \ReflectionClass($transformer);
            $attribs = $reflection->getAttributes();
            foreach ($attribs as $attrib) {
                $instance = $attrib->newInstance();
                $this->transformers[$instance->name] = $transformer;
            }
        }
    }

    /**
     * Creates and returns a transformer instance based on the provided
     * transformer name.
     *
     * @throws \InvalidArgumentException If the transformer is not found.
     */
    public function create(string $transformer): TransformerInterface {
        if (!isset($this->transformers[$transformer])) {
            throw new \InvalidArgumentException(sprintf('Transformer "%s" not found.', $transformer));
        }
        $class = $this->transformers[$transformer];
        if ($class instanceof TransformerInterface) {
            return $class;
        }
        $arrayTransformer = new ArrayTransformer();
        return new $class($arrayTransformer);
    }

}
