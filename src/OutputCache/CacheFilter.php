<?php

namespace DC\Router\OutputCache;

class CacheFilter implements \DC\Router\IGlobalFilter {
    /**
     * @var \DC\Cache\ICache
     */
    private $cache;
    /**
     * @var IKeyGenerator
     */
    private $keyGenerator;

    /**
     * @var Reflector
     */
    private $reflector;

    function __construct(\DC\Cache\ICache $cache, IKeyGenerator $keyGenerator = null)
    {
        $this->cache = $cache;
        $this->keyGenerator = isset($keyGenerator) ? $keyGenerator : new DefaultKeyGenerator();
        $this->reflector = new Reflector();

        \phpDocumentor\Reflection\DocBlock\Tag::registerTagHandler("cache", '\DC\Router\OutputCache\Tag\CacheTag');
        \phpDocumentor\Reflection\DocBlock\Tag::registerTagHandler("cache-vary", '\DC\Router\OutputCache\Tag\CacheVaryTag');
    }

    /**
     * @param callable $callable
     * @param $tag
     * @return \phpDocumentor\Reflection\DocBlock\Tag[]
     * @throws \ReflectionException
     */
    private function getTag($callable, $tag) {
        $reflection = $this->reflector->getReflectionFunctionForCallable($callable);
        $phpdoc = new \phpDocumentor\Reflection\DocBlock($reflection);
        $tags = $phpdoc->getTagsByName($tag);
        return count($tags) > 0 ? $tags[0] : null;
    }


    /**
     * @param callable $callable
     * @param array $params
     * @return string[]
     * @throws \ReflectionException
     */
    private function removeParams($callable, array $params) {
        $reflection = $this->reflector->getReflectionFunctionForCallable($callable);
        $phpdoc = new \phpDocumentor\Reflection\DocBlock($reflection);
        /** @var \DC\Router\OutputCache\Tag\CacheVaryTag[] $varies */
        $varies = $phpdoc->getTagsByName("cache-vary");

        if (count($varies) == 0) {
            return $params;
        }

        $allowed = [];
        foreach ($varies as $vary) {
            $allowed = array_merge($allowed, $vary->getParameters());
        }
        $allowed = array_flip($allowed);
        return array_intersect_key($params, $allowed);
    }

    private function keyFromRouteAndParams(\DC\Router\IRoute $route, array $params, &$expires) {
        $callable = $route->getCallable();
        /** @var \DC\Router\OutputCache\Tag\CacheTag $tag */
        $tag = $this->getTag($callable, "cache");
        if ($tag) {
            $expires = $tag->getExpiry();
            $params = $this->removeParams($callable, $params);
            return $this->keyGenerator->fromCallableAndParams($callable, $params);
        }
    }

    /**
     * @inheritdoc
     */
    function beforeRouteExecuting(\DC\Router\IRequest $request, \DC\Router\IRoute $route, array $params, array $rawParams)
    {

    }

    /**
     * @inheritdoc
     */
    function routeExecuting(\DC\Router\IRequest $request, \DC\Router\IRoute $route, array $params, array $rawParams)
    {
        $expires = null;
        if ($key = $this->keyFromRouteAndParams($route, $rawParams, $expires)) {
            $response = $this->cache->get($key);
            if ($response instanceof \DC\Router\IResponse) {
                return $response;
            }
        }
    }

    /**
     * @inheritdoc
     */
    function afterRouteExecuting(\DC\Router\IRequest $request, \DC\Router\IRoute $route, array $params, array $rawParams, \DC\Router\IResponse $response)
    {

    }

    /**
     * @inheritdoc
     */
    function afterRouteExecuted(\DC\Router\IRequest $request, \DC\Router\IRoute $route, array $params, array $rawParams, \DC\Router\IResponse $response)
    {
        $expires = null;
        if ($key = $this->keyFromRouteAndParams($route, $rawParams, $expires)) {
            $this->cache->set($key, $response, $expires);
        }
    }
}