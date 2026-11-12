## Overview

The Router package is used to register an application's routes and to route the request URI to a controller method.

### Adding maps

The purpose of a router is to find a controller based on a routing path. The path could be a URL for a web site, or it could be an
end-point for a RESTful web-services API.

The `addRoute` method is used to map at routing pattern to a controller.

```php
use Joomla\Router\Router;

$router = new Router;
$router
    ->addRoute('GET', '/article/:article_id', 'Acme\\ArticleController') // Route to Controller
	->addRoute('GET', '/component/*', function() { return true; }) // Route to Closure
	->addRoute('GET', '/user/:id', 'get_user'); // Route to function / callable

function get_user($id)
{
    // do stuff
}
```

By default, named variables in the defined route have a matching regex of `([^/]*)`, which matches everything in a route, up to the next `/`.
You can optionally define the exact regex to use, by passing a third parameter to the `addMaps` function. This is an associative array using
the named variables as the keys and the desired regex as the values.

```php
use Joomla\Router\Router;

$router = new Router;
$router->addRoute(
    'GET',
    '/user/:id',
    'UserController@show',
    array(
        'id' => '(\d+)'
    )
);
```

If you were to take the above code, and match it against a path of `/user/123`, you would receive the following array back.

```php
$match = $router->parseRoute('/user/123');

print_r($match);
// Array
// (
//     [controller] => UserController@show
//     [vars] => Array
//         (
//             [name] => user
//         )
// )
```

At this point, you can do whatever you want with the returned data. It's up to you to instantiate your controller, or just use
`call_user_func_array($matched['controller'], $matched['vars'])` in your FrontController if you're using the Router as the backbone
for a micro framework. It's completely flexible.

### Request methods

Each route lists the HTTP methods it supports (`get()`, `post()`, … register a single method, `all()` accepts any method).
`parseRoute()` checks the routes in the order they were added and returns the first route whose pattern matches the path **and**
which supports the request method. The same pattern can therefore be registered separately for several methods:

```php
$router->get('/articles', 'Acme\\ArticlesListController');
$router->post('/articles', 'Acme\\ArticlesCreateController');

$router->parseRoute('/articles', 'POST'); // Resolved to Acme\ArticlesCreateController
```

If at least one route matches the path but none of them supports the request method, a
`Joomla\Router\Exception\MethodNotAllowedException` (code 405) is thrown. Its `getAllowedMethods()` returns the methods of all
matching routes, e.g. `['GET', 'POST']` for a `DELETE` request in the example above, which can be used for the `Allow` response
header. If no route matches the path at all, a `Joomla\Router\Exception\RouteNotFoundException` (code 404) is thrown.

By default, a HEAD request is only routed to routes that support HEAD. HTTP expects HEAD to be supported wherever GET is
([RFC 9110, section 9.3.2](https://www.rfc-editor.org/rfc/rfc9110#section-9.3.2)); call `setServeHeadWithGet(true)` to resolve
a HEAD request to the GET route when no route matching the path supports HEAD. A route registered for HEAD is still preferred,
and the allowed methods of a `MethodNotAllowedException` then include `HEAD` wherever they include `GET`. The controller is
responsible for not sending a response body.

```php
$router->setServeHeadWithGet(true);

$router->parseRoute('/articles', 'HEAD'); // Resolved to Acme\ArticlesListController
```

### Decoding route variables

The values of route variables are returned as they appear in the path, so `/tag/foo%20bar` gives `['tag' => 'foo%20bar']`. Call
`setDecodeRouteVariables(true)` to decode them with `rawurldecode()` instead, which gives `['tag' => 'foo bar']`. Default values
are not decoded. Decoding happens after matching, so a variable can then contain a `/` that was sent encoded as `%2F`; check
the value if it is used as a path.

```php
$router->setDecodeRouteVariables(true);
```
