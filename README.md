<p align="center">
    <img src="https://railt.org/images/logo-dark.svg" width="200" alt="Railt" />
</p>

<p align="center">
    <a href="https://travis-ci.org/railt/discovery"><img src="https://travis-ci.org/railt/discovery.svg?branch=master" alt="Travis CI" /></a>
    <a href="https://scrutinizer-ci.com/g/railt/discovery/?branch=master"><img src="https://scrutinizer-ci.com/g/railt/discovery/badges/coverage.png?b=master" alt="Code coverage" /></a>
    <a href="https://scrutinizer-ci.com/g/railt/discovery/?branch=master"><img src="https://scrutinizer-ci.com/g/railt/discovery/badges/quality-score.png?b=master" alt="Scrutinizer CI" /></a>
    <a href="https://packagist.org/packages/railt/discovery"><img src="https://poser.pugx.org/railt/discovery/version" alt="Latest Stable Version"></a>
    <a href="https://packagist.org/packages/railt/discovery"><img src="https://poser.pugx.org/railt/discovery/v/unstable" alt="Latest Unstable Version"></a>
    <a href="https://raw.githubusercontent.com/railt/discovery/master/LICENSE.md"><img src="https://poser.pugx.org/railt/discovery/license" alt="License MIT"></a>
</p>

# Installation

- Install package using composer.

```bash
composer require railt/discovery
```

- Add discovering event into your `composer.json`.

```json
{
    "scripts": {
         "post-autoload-dump": [
             "Railt\\Discovery\\Manifest::discover"
         ]
     }
}
```

## Usage

Railt Discover provides the ability to implement a cross-package 
configuration using `composer.json`.

In order to access the configuration group, you must specify the key 
name in the `extra` section:

```json
{
    "extra": {
        "discovery": ["railt"]
    }
}
```

In this case, the "`railt`" section is exported to the project. So the 
data from the `railt` section of *all packages* used in your application 
will be available.

As example:
```json
{
    "name": "vendor/package-1",
    "extra": {
        "railt": { "commands": [ "Vendor\\Package1\\ConsoleScript" ] }
    }
}
```
```json
{
    "name": "vendor/package-2",
    "extra": {
        "railt": { "commands": [ "Vendor\\Package2\\ConsoleScript2" ] }
    }
}
```

In order to get the data, you need to call the `get` method of the `Discovery` class.

```php
<?php

$loader = __DIR__ . '/vendor/autoload.php';

$discovery = Railt\Discovery\Discovery::fromClassLoader($loader);
$commands = $discovery->get('railt.commands');

\var_dump($commands); 
// Will output:
// array (
//  'Vendor\Package1\ConsoleScript',
//  'Vendor\Package2\ConsoleScript2'
// )
```
