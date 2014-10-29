<?php

namespace DC\Router\OutputCache;

interface IKeyGenerator {
    function fromCallableAndParams(callable $callable, array $params);
} 