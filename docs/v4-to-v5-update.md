## Updating from v4 to v5

The following changes were made to the Router package between v4 and v5.

### `Router` and `Route` no longer implement `\Serializable`

PHP's `\Serializable` interface is deprecated since PHP 8.1. `Router` and `Route` no longer implement it, and their
`serialize()` and `unserialize()` methods, deprecated in 4.x, have been removed. Serializing works as before through
`__serialize()` and `__unserialize()`: pass the object to `serialize()` and the string to `unserialize()`.

```php
// Before
$data = $router->serialize();
$copy = new Router;
$copy->unserialize($data);

// After
$data = serialize($router);
$copy = unserialize($data);
```

Code that checks `$router instanceof \Serializable` has to be adjusted.

<!-- Section: Native types for parseRoute() -->
