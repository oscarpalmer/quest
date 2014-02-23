# Quest

Quest is a router for PHP.

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

- Errors.
- Filters.
- Request and Response helper methods.
- More and better documentation.

## License

MIT Licensed; see [the LICENSE file](LICENSE) for more info.