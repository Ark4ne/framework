<?php

namespace Luxury\Http\Middleware;

use Luxury\Constants\Events as EventSpaces;
use Luxury\Foundation\Middleware\Controller as ControllerMiddleware;
use Luxury\Middleware\AfterMiddleware;
use Luxury\Middleware\BeforeMiddleware;
use Luxury\Security\RateLimiter;
use Phalcon\Http\Response\StatusCode;

/**
 * Class Throttle
 *
 * @package Luxury\Middleware
 *
 * @property-read \Phalcon\Cache\BackendInterface cache
 * @property-read \Phalcon\Mvc\Application        app
 */
class Throttle extends ControllerMiddleware implements BeforeMiddleware, AfterMiddleware
{
    /**
     * Number of max request by $decay
     *
     * @var int
     */
    private $max;

    /**
     * Decay time (seconds)
     *
     * @var int
     */
    private $decay;

    /**
     * @var RateLimiter
     */
    private $limiter;

    /**
     * Throttle constructor.
     *
     * @param int $max   Number of max request by $decay
     * @param int $decay Decay time (seconds)
     */
    public function __construct($max, $decay = 60)
    {
        parent::__construct();

        $this->max     = $max;
        $this->decay   = $decay;
        $this->limiter = new RateLimiter();
        $this->limiter->setDI($this->getDI());
    }

    /**
     * Called before the execution of handler
     *
     * @param \Phalcon\Events\Event|mixed $event
     * @param \Phalcon\Dispatcher|mixed   $source
     * @param mixed|null                  $data
     *
     * @throws \Exception
     * @return bool
     */
    public function before($event, $source, $data = null)
    {
        $signature = $this->resolveRequestSignature();

        if ($this->limiter->tooManyAttempts($signature, $this->max, $this->decay)) {
            $this->buildResponse($signature, true);

            return false;
        }

        $this->limiter->hit($signature, $this->decay);

        return true;
    }

    /**
     * Called after the execution of handler
     *
     * @param \Phalcon\Events\Event|mixed $event
     * @param \Phalcon\Dispatcher|mixed   $source
     * @param mixed|null                  $data
     *
     * @throws \Exception
     * @return bool
     */
    public function after($event, $source, $data = null)
    {
        $this->buildResponse($this->resolveRequestSignature(), false);
    }

    /**
     * Resolve the request signature based on :
     *  Module : Namespace : Controller : Action | HOST | URI | ClientIP
     *
     * @return string
     */
    private function resolveRequestSignature()
    {
        $request = $this->request;
        $router  = $this->router;

        return sha1(
            $router->getModuleName() .
            ':' . $router->getNamespaceName() .
            ':' . $router->getControllerName() .
            ':' . $router->getActionName() .
            '|' . $request->getHttpHost() .
            '|' . $request->getURI() .
            '|' . $request->getClientAddress()
        );
    }

    /**
     * Add the limit header information to the response.
     *
     * @param string $key
     * @param bool   $tooManyAttempts Bind specific values when there are too many attempts
     *
     * @return \Phalcon\Http\Response|\Phalcon\Http\ResponseInterface
     */
    private function buildResponse($key, $tooManyAttempts = false)
    {
        $response = $this->response;

        $response->setHeader('X-RateLimit-Limit', $this->max);
        $response->setHeader(
            'X-RateLimit-Remaining',
            $this->limiter->retriesLeft($key, $this->max, $this->decay)
        );

        if ($tooManyAttempts) {
            $response->setHeader('X-RateLimit-Remaining', 0);

            $msg = StatusCode::message(StatusCode::TOO_MANY_REQUESTS);

            $response->setContent($msg);
            $response->setStatusCode(StatusCode::TOO_MANY_REQUESTS, $msg);
            $response->setHeader('Retry-After', $this->limiter->availableIn($key, $this->decay));
        }

        return $response;
    }
}
