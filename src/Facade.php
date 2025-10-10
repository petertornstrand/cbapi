<?php

namespace App;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Facade.
 */
final class Facade
{
    private static ?Facade $instance = null;
    private static ContainerInterface $container;

    /**
     * Facade constructor.
     */
    private function __construct(ContainerInterface $container)
    {
        self::$container = $container;
    }

    /**
     * Create a new service instance.
     *
     * @param string $serviceId
     *
     * @return object
     * @throws \Exception
     */
    public static function create(string $serviceId): object
    {
        if (null === self::$instance) {
            throw new \Exception('Facade is not instantiated');
        }

        return self::$container->get($serviceId);
    }

    /**
     * Initialize the facade.
     *
     * @param ContainerInterface $container
     *
     * @return Facade
     */
    public static function init(ContainerInterface $container): self
    {
        if (null === self::$instance) {
            self::$instance = new self($container);
        }

        return self::$instance;
    }
}
