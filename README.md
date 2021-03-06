# Quest

[![PHP version](https://badge.fury.io/ph/oscarpalmer%2Fquest.svg)](http://badge.fury.io/ph/oscarpalmer%2Fquest) [![Build Status](https://travis-ci.org/oscarpalmer/quest.png?branch=master)](https://travis-ci.org/oscarpalmer/quest) [![Coverage Status](https://codecov.io/gh/oscarpalmer/quest/branch/master/graph/badge.svg)](https://codecov.io/gh/oscarpalmer/quest)

Quest is a router for PHP `>=7`.

## The name

> In particular, questing heroes of all stripes seek after the fabled Daedric artifacts for their potent combat and magical benefits.

&mdash; Haderus of Gottlesfont, _[Modern Heretics](http://uesp.net/wiki/Lore:Modern_Heretics)_

## Getting started

### Installation

Quest is available via [Composer & Packagist](//packagist.org/packages/oscarpalmer/quest).

```json
{
  "require": {
    "oscarpalmer/quest": "2.2.*",
    "oscarpalmer/shelf": "2.3.*"
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

### Examples of routes

```php
# the following route will match /a/simple/path
$quest->get("/a/simple/path", $callback);

# the following route will match
# /path/to/dir/file.extension and /a/b/c/d/e/f/b/g/h.i
$quest->get("/*/:file.:ext", $callback);

# optional parameters should be wrapped in parentheses;
# the following route will therefore match both
# /path/to/dir/file.extension and /path/to/dir/file
$quest->get("/*/:file(.:ext)", $callback);
```

### Callbacks

Echoes are captured and flushed. Please use return statements to output stuff.

### Error handlers

Having an error handler ready for when you or the user hits a bump is always useful. Status-specific handlers takes precedence over the wildcard handler, which in turn take precedence over the default handlers.

```php
# Calling handlers
$quest->error();    # The error-handler defaults to 500.
$quest->error(401); # You can choose the error, too.

# Defining handlers
$quest->error(401, function () {}); # Status-specific errors are possible, too.
```

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
$quest = new Quest($a, $b, $c);     #  Optional parameters; array of routes, and Request
                                    #  and Response objects from Shelf; useful for testing

# Constants and properties
$quest::VERSION;                    #  Current Quest version number
$quest->errors;                     #  Array of error callbacks with status codes as keys
$quest->filters;                    #  Array of filters with at most two children; "after" and "before"
$quest->parameters;                 #  Object of route parameters
$quest->request;                    #  Shelf Request object
  $quest->cookies;                  #    $_COOKIE object belonging to the Request object
  $quest->data;                     #    $_POST object
  $quest->files;                    #    $_FILES object
  $quest->query;                    #    $_GET object
  $quest->server;                   #    $_SERVER object
  $quest->session;                  #    $_SESSION object
$quest->response;                   #  Shelf Response object
$quest->routes;                     #  Array of routes

# Filter methods
$quest->after($path, $callback);    #  Add an after filter with a path to run after routing
$quest->before($path, $callback);   #  Add a before filter with a path to run before routing
                                    #  $path must be a string, and $callback must be a callable

# Route methods
$quest->delete($path, $callback);   #  Add a DELETE route;
$quest->get($path, $callback);      #  Add a GET (and HEAD) route
$quest->post($path, $callback);     #  Add a POST route
$quest->put($path, $callback);      #  Add a PUT route
                                    #  $path must be a string and $callback must be a callable

# Error method
$quest->error($status, $callback);  #  Add or run an error callback; will run an already defined
                                    #  or default callback if no $callback is supplied
                                    #  $status can be a valid status code or null (500 error);
                                    #  $callback must be a callable if supplied

# Helper methods
$quest->contentType($optional);     #  Get or set the content type of the response
$quest->header($name, $optional);   #  Get or set a response header

# Run method
$quest->run();                      #  Let the questing (routing) begin!
                                    #  Will run filters and the first matching route's callback
```

## License

MIT Licensed; see [the LICENSE file](LICENSE) for more info.
