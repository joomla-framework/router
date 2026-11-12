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

## Things to know before you build on this

**One pattern cannot serve several HTTP methods.** `parseRoute()` walks the routes in registration
order and throws `MethodNotAllowedException` at the *first* route whose pattern matches but whose
method does not — it never looks further. So this makes the second route unreachable:

```php
$router->get('/items', ListController::class);
$router->post('/items', CreateController::class);   // never matched
```

Register the pattern once with `all()` and branch on the method inside the controller:

```php
$router->all('/items', ItemsController::class);
```

**A regex failure looks like "no match".** The return value of `preg_match()` is not checked, so a
pattern that hits the PCRE backtrack limit — a long path against a rule with nested quantifiers —
is treated as a non-matching route rather than an error. The request then falls through to a later
route or a 404. Keep the rules in `custom-vars.md` simple and anchored.

**Route variables are not URL-decoded.** `parseRoute()` matches the raw path and hands the captured
values through unchanged, so `%2F` stays encoded and reaches the controller that way. If you decode
downstream, an encoded slash turns into a path separator after the routing decision was made —
reject it explicitly if the value becomes a path.

**Route variables lose against request variables.** `WebApplication` merges them into the input
with `def()`, so a query string `?id=99` beats the `/items/5` segment. Read from the
`ResolvedRoute` when the route value must win.

**`parse_url()` failures are not caught.** A malformed request URI makes `parse_url()` return
`false`, which becomes an empty path — and an empty path can match a root route.
