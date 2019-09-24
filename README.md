# mercure-behavior

![Latest Stable Version](https://img.shields.io/packagist/v/bizley/mercure-behavior)
[![Total Downloads](https://img.shields.io/packagist/dt/bizley/mercure-behavior)](https://packagist.org/packages/bizley/mercure-behavior)
![License](https://img.shields.io/packagist/l/bizley/mercure-behavior)

Yii 2 Mercure behavior
----------------------

This package provides Yii 2 behavior to automatically publish updates to the Mercure hub when resource is being modified.  
The purpose of this behavior is to equip Yii application with the same Mercure functionality as given by 
[API Platform](https://api-platform.com/docs/core/mercure/).

What is Mercure?
----------------

Quoting [dunglas/mercure](https://github.com/dunglas/mercure):
> Mercure is a protocol allowing to push data updates to web browsers and other HTTP clients in a convenient, fast, 
> reliable and battery-efficient way. It is especially useful to publish real-time updates of resources served through 
> web APIs, to reactive web and mobile apps.

See the linked repository to find out more about Mercure. There are also instructions how to set up the server and 
the client to establish connection using Mercure protocol.

Installation
------------

Add the package to your `composer.json`:

    {
        "require": {
            "bizley/mercure-behavior": "^1.0"
        }
    }

and run `composer update` or alternatively run `composer require bizley/mercure-behavior:^1.0`

You will of course need Mercure Hub as well. Refer to [dunglas/mercure](https://github.com/dunglas/mercure) for the 
instructions how to get one (I recommend using Docker image).

Usage
-----

Add this [behavior](https://www.yiiframework.com/doc/guide/2.0/en/concept-behaviors) to the resource object you want to 
be subject of Mercure updates (usually it's an Active Record instance).

```php
use \bizley\yii2\behaviors\mercure\MercureBehavior;

public function behaviors()
{
    return [
        MercureBehavior::class,
    ];
}
```

Resource object must implement `\bizley\yii2\behaviors\mercure\MercureableInterface`.  
By default MercureBehavior will dispatch update to Mercure Hub in JSON format after the resource has been
successfully created, updated, or deleted, using the Mercure publisher component registered under the 'publisher'
name.

You can customize the configuration according to your needs, for example:

```php
public function behaviors()
{
    return [
        [
            'class' => MercureBehavior::class,
            'publisher' => \bizley\yii2\mercure\Publisher::class,
            'format' => \yii\web\Response::FORMAT_XML
        ]
    ];
}
```

Publishing the updates
----------------------

The behavior is using [yii2-mercure](https://github.com/bizley/yii2-mercure) package for publishing the updates.  
Please follow the repository link to learn how to configure it properly.
