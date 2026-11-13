## Updating from v4 to v5

The following changes were made to the Router package between v4 and v5.

<!-- Section: Router and Route no longer implement \Serializable -->

### `parseRoute()` has native types

`RouterInterface::parseRoute()` and `Router::parseRoute()` now declare their parameter and return types:

```php
// Before
public function parseRoute($route, $method = 'GET');

// After
public function parseRoute(string $route, string $method = 'GET'): ResolvedRoute;
```

Classes implementing `RouterInterface` or extending `Router` and overriding `parseRoute()` must use the same signature. Passing
`null` or a non-scalar value as the route or method now throws a `TypeError`.
