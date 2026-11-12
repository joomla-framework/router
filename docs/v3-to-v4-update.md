# Updating from v3 to v4

Release 4.0.0 raises the PHP requirement and replaces the library used to serialize closure
controllers.

## At a glance

| | v3 (3.0.1) | v4 (4.0.0) |
|---|---|---|
| PHP | `^8.1.0` | `^8.3.0` |
| Closure serialization | `jeremeamia/superclosure` | `opis/closure` |
| Public API | — | unchanged |

## Minimum supported PHP version raised

All Framework packages now require **PHP 8.3** or newer.

## Closure serialization moved to `opis/closure`

`Route` implements `Serializable` so a router can be cached. A controller given as a `Closure`
cannot be serialized by PHP itself, so the package delegates that to an optional library. Until
3.x this was `jeremeamia/superclosure`, which is no longer maintained; 4.0.0 uses `opis/closure`.

```php
// v3 — the error when the library was missing
// "Install the "jeremeamia/superclosure" package to serialize Closures."

// v4
// "Install the "opis/closure" package to serialize Closures."
```

If you serialize routers with closure controllers, swap the dependency:

```bash
composer remove jeremeamia/superclosure
composer require opis/closure
```

Nothing changes for routers whose controllers are class names or arrays — the common case — and
no method signature changed.

> Serialized routers containing closures are executable code. `opis/closure` reconstructs a closure
> from its source, so a router cache that anyone can write to is a code execution path. Keep it out
> of shared or web-writable locations.

## Dependency changes

| Package | v3 (3.0.1) | v4 (4.0.0) |
|---|---|---|
| `php` | `^8.1.0` | `^8.3.0` |

Both libraries are optional and live in `suggest`: `opis/closure` (new) and `joomla/console` for
the `debug:router` command.
