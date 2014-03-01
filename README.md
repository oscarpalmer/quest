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
    "oscarpalmer/quest": "dev-master"
  }
}
```

### Basic usage

```php
use oscarpalmer\Quest\Quest;

$quest = new Quest;

$quest->get("/", function () {
    return "Hello, world!";
});

$quest->run();
```

## Todo

- Request and Response helper methods.
- API documentation.
- Improved documentation.

## License

MIT Licensed; see [the LICENSE file](LICENSE) for more info.