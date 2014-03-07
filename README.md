# Quest

[![Build Status](https://travis-ci.org/oscarpalmer/quest.png?branch=master)](https://travis-ci.org/oscarpalmer/quest) [![Coverage Status](https://coveralls.io/repos/oscarpalmer/quest/badge.png?branch=master)](https://coveralls.io/r/oscarpalmer/quest?branch=master)

Quest is a router for PHP `>=5.3`.

## The name

> In particular, questing heroes of all stripes seek after the fabled Daedric artifacts for their potent combat and magical benefits.

&mdash; Haderus of Gottlesfont, [Modern Heretics](http://uesp.net/wiki/Lore:Modern_Heretics).

Quests are cool. I like quests and questing.

## Getting started

### Installation

Quest is available via Composer.

```json
{
  "require": {
    "oscarpalmer/quest": "dev-master",
    "oscarpalmer/shelf": "dev-master"
  }
}
```

Quest uses [Shelf](//github.com/oscarpalmer/shelf), a Rack-like interface for PHP made by me. It's pretty cool.

### Basic usage

```php
use oscarpalmer\Quest\Quest;

$quest = new Quest;

$quest->get("/", function () {
    return "Hello, world!";
});

$quest->run();
```

And that's it! But I'm sure you'll want to know more about [routing](#routing) and the [API](#api).

## Routing

If you've ever used something like [Sinatra](http://sinatrarb.com), then routing with Quest shouldn't be too difficult.

There are three kinds of routing parameters that'll make working with complex routes a piece of cake.

- `*`: matches just about everything; i.e `.*?`.
- `:param`: matches any word character; i.e `\w+`.
- `(anything)`: optional match for `anything`; i.e. `(anything)?`.

### Examples

```php
# the following route will match /a/simple/path
$quest->get("/a/simple/path", $callback);

# the following route will match
# /path/to/dir/file.extension and /a/b/c/d/e/f/b/g/h.i
$quest->get("/*/:file.:ext", $callback);

# optional parameters should be wrapped in parentheses;
# the following route will therefore match both
# /path/to/dir/file.extension and /path/to/dir
$quest->get("/*/:file(.:ext)", $callback);
```

### Callbacks

Echoes are captured and flushed. Please use return statements to output stuff.

### Server stuff

Now, Quest can't do much if your server isn't set up properly, so here's two snippets for "proper" URL rewriting with [Apache](http://httpd.apache.org) and [nginx](http://nginx.org).

#### Apache

```apache
RewriteEngine on
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule . /index.php [L]
```

#### nginx

```nginx
try_files $uri /index.php;
```

## API

```php
# Constructor
$quest = new Quest($a, $b, $c);     # Optional parameters; array of routes, and Request
                                    # and Response objects from Shelf; useful for testing.

# Constants and properties
$quest::VERSION;                    # Current Quest version number.
$quest->errors;                     # Array of error callbacks with status codes as keys.
$quest->filters;                    # Array of filters with at most two children; "after" and "before".
$quest->request;                    # Shelf Request object.
$quest->response;                   # Shelf Response object.
$quest->routes;                     # Array of routes.

# Filter methods
$quest->after($callback);           # Add an after filter without a path to run after routing.
$quest->after($path, $callback);    # Add an after filter with a path to run after routing.
$quest->before($callback);          # Add an after filter without a path to run after routing.
$quest->before($path, $callback);   # Add a before filter with a path to run before routing.
                                    # $path must be a string, and $callback must be a callable.

# Route methods
$quest->delete($path, $callback);   # Add a DELETE route;
$quest->get($path, $callback);      # Add a GET (and HEAD) route.
$quest->post($path, $callback);     # Add a POST route.
$quest->put($path, $callback);      # Add a PUT route.
                                    # $path must be a string and $callback must be a callable.

# Error method
$quest->error($status, $callback);  # Add or run an error callback; will run an already defined
                                    # or default callback if no $callback is supplied.
                                    # $status must be an integer and valid status code and
                                    # $callback must be a callable if supplied.

# Run method
$quest->run();                      # Let the questing (routing) begin!
                                    # Will run filters and the first matching route's callback.
```

## Todo

- Helper methods.

## License

MIT Licensed; see [the LICENSE file](LICENSE) for more info.