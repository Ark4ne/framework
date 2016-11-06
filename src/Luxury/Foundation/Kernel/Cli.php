<?php

namespace Luxury\Foundation\Kernel;

use Luxury\Foundation\Kernelize;
use Luxury\Interfaces\Kernelable;
use Phalcon\Cli\Console;
use Phalcon\Di\FactoryDefault\Cli as Di;

/**
 * Class Cli
 *
 * @package Luxury\Foundation\Kernel
 *
 * @property-read \Luxury\Cli\Router $router
 * @property-read \Phalcon\Cli\Dispatcher $dispatcher
 */
abstract class Cli extends Console implements Kernelable
{
    use Kernelize;

    /**
     * Return the Provider List to load.
     *
     * @var string[]
     */
    protected $providers = [];

    /**
     * Return the Middlewares to attach onto the application.
     *
     * @var string[]
     */
    protected $middlewares = [];

    /**
     * Return the Events Listeners to attach onto the application.
     *
     * @var string[]
     */
    protected $listeners = [];

    /**
     * The DependencyInjection class to use.
     *
     * @var string
     */
    protected $dependencyInjection = Di::class;

    /**
     * Application constructor.
     */
    public function __construct()
    {
        parent::__construct(null);
    }

    /**
     * Register the routes of the application.
     */
    public function registerRoutes()
    {
        require $this->config->paths->routes . 'cli.php';
    }
}
