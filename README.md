# Ride: Web CMS Varnish ORM

ORM Varnish integration for the CMS of a Ride web application.

This module adds a event listener to ORM entry save and delete actions.
When an entry is saved or deleted, this module resolves the changed pages and bans those URL's in the Varnish cache.

## Related Modules

- [ride/app](https://github.com/all-ride/ride-app)
- [ride/app-orm](https://github.com/all-ride/ride-app-orm)
- [ride/app-varnish](https://github.com/all-ride/ride-app-varnish)
- [ride/lib-cms](https://github.com/all-ride/ride-lib-cms)
- [ride/lib-event](https://github.com/all-ride/ride-lib-event)
- [ride/lib-orm](https://github.com/all-ride/ride-lib-orm)
- [ride/lib-reflection](https://github.com/all-ride/ride-lib-reflection)
- [ride/lib-varnish](https://github.com/all-ride/ride-lib-varnish)
- [ride/web](https://github.com/all-ride/ride-web)
- [ride/web-cms](https://github.com/all-ride/ride-web-cms)
- [ride/web-cms-varnish](https://github.com/all-ride/ride-web-cms-varnish)
- [ride/web-orm](https://github.com/all-ride/ride-web-orm)

## Installation

You can use [Composer](http://getcomposer.org) to install this application.

```
composer require ride/web-cms-orm-varnish
```

