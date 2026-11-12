## Serializing the Router

A `Router` and its `Route` objects can be serialized with PHP's `serialize()`, for example to cache the route map between
requests:

```php
use Joomla\Router\Router;

$router = new Router;
$router->get('/articles/:id', 'Acme\\ArticleController');

file_put_contents($cacheFile, serialize($router));

// On the next request
$router = unserialize(file_get_contents($cacheFile));
```

Controllers given as class names, strings or arrays are serialized as they are. PHP cannot serialize a `Closure`, so a route with a
Closure controller needs the optional [`opis/closure`](https://github.com/opis/closure) package:

```bash
composer require opis/closure
```

With the package installed, the Closure is serialized by `opis/closure` and restored when the route is unserialized. Without it,
serializing such a route throws a `RuntimeException`, and so does unserializing it in an environment where the package is missing.

`Router` and `Route` still implement PHP's deprecated `\Serializable` interface. Its methods `serialize()` and `unserialize()`
are not used by PHP 8 (it calls `__serialize()` and `__unserialize()`) and are deprecated: calling them directly triggers a
deprecation, and they will be removed together with the interface in 5.0. Pass the object to `serialize()` and the string to
`unserialize()` instead:

```php
// Deprecated
$data = $router->serialize();
$copy = new Router;
$copy->unserialize($data);

// Use
$data = serialize($router);
$copy = unserialize($data);
```

A serialized Closure is executable code: unserializing it recreates the function from its source. Only unserialize data you wrote
yourself, keep the cache out of shared or web-writable locations, or configure a security provider for `opis/closure` to sign
the data.
