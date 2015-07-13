# DoctrineCacheSweeperBundle #

## About ##

This bundle provides a way to implement cache invalidation strategies whenever changes are flushed to the database.

## Installation ##

Use composer…

```bash
$ composer require "michelv/doctrine-cache-sweeper-bundle"
```

…then enable the bundle in AppKernel:

```php
<?php
// app/AppKernel.php

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new Michelv\DoctrineCacheSweeperBundle\MichelvDoctrineCacheSweeperBundle(),
        );
    }
}
?>
```

## Principle ##

### Why? ###

You want to use this bundle when simply setting a TTL on cache keys is not enough.

### How it works ###

When the event `onFlush` is triggered, `CacheSweeper` gathers the cache keys to invalidate for each updated entity by calling the method `getSweepableKeys()` of the repositories that implement the interface `CacheSweepableInterface`.

## Usage ##

First, implement `CacheSweepableInterface` in the repositories where you use cache keys.

The second argument for the method `getSweepableKeys()` is the type of update that will be applied. You may either disregard this argument and always return the same array of keys, or handle different use cases.

Since the first argument is the updated entity, you can also manually call `getSweepableKeys()` on a related entity's repository even if that related entity hasn't been updated. (Of course, you need to make sure not to run into a circular dependency.)

For example, if you have a cache key for the last blog posts in a category, you have the possibility to get the cache keys related to the category when `CacheSweeper` gathers the cache keys related to a new post.

## Advanced usage ##

You can extend the `CacheSweeper` class and use your own by setting the parameter `michelv_doctrine_cache_sweeper.class` in your app's config, like this:

```yaml
# app/config/config.yml
parameters:
    michelv_doctrine_cache_sweeper.class: ACME\Bundle\CacheSweeper
```

You can also define cache keys to invalidate in services, as long as they implement `CacheSweepableInterface`. For this use case, you will need to call `addService()` from your app's config, like this:

```yaml
# app/config/services.yml
services:
    michelv.doctrine_cache_sweeper:
        class: %michelv_doctrine_cache_sweeper.class%
        tags:
            - { name: 'doctrine.event_listener', event: onFlush }
        calls:
            - [addService, [@acme_foo]]
            - [addService, [@acme_bar]]
```

## Author ##

Michel Valdrighi - hello@miche.lv - http://miche.lv/

## License ##

DoctrineCacheSweeperBundle is licensed under the MIT License - see the LICENSE file for details.

## Todo ##

* proper configuration support (to avoid having to redefine the service when calling `addService()`)
* tests
