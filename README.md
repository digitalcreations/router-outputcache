## Installation

```
$ composer install dc/router-outputcache
```

Or add it to `composer.json`:

```json
"require": {
	"dc/router-outputcache": "0.*",
	"dc/cache-memcache": ">=0.2"
}
```

```
$ composer install
```

## Using

Before calling `\DC\Router\IoC\RouterSetup::setup()`, register the two following items with the IoC container:

```php
$container
  ->register('\DC\Router\OutputCache\DefaultKeyGenerator')
  ->to('\DC\Router\OutputCache\IKeyGenerator');
$container
  ->register('\DC\Router\OutputCache\CacheFilter')
  ->to('\DC\Router\IGlobalFilter');
\DC\Router\IoC\RouterSetup::setup($container);
```

When this is done, you can decorate any routes in your controllers like this:

```php
class KittenController extends \DC\Router\ControllerBase {

  /**
   * @route GET /kittens
   * @cache +1 hour
   */  
  function getAll() {
    return [
		// making kittens is left as an exercise for the reader
	];
  }

  /**
   * @route GET /kitten/{id:int}
   * @cache +30 seconds
   * @cache-vary $id
   */  
  function getKitten($id) {
    return new Kitten($id);
  }
}
```

## `@cache`

You can specify the `@cache` tag on any route. The response will be cached Optionally you can specify a time interval (anything accepted by `strtotime`). By default it caches for 1 hour.

## `@cache-vary`

This enables you to target only some of the parameters to generate the cache key. By default all query parameters are used.

In the following case the `name` is not used in the lookup, but only to make the URL pretty. Thus, it does not deserve to be part of the cache key, and this would produce a cache hit even if the kitten's name changes.

```php
  /**
   * @route GET /kittens/{name}/{id:int}
   * @cache
   * @cache-vary $id
   */
  function getKitten($id, $name) {
     return new Kitten($id);
  }
```
