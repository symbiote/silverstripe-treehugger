# TreeHugger (Sortable Menus)

[![Build Status](https://travis-ci.org/silbinarywolf/silverstripe-treehugger.svg?branch=master)](https://travis-ci.org/silbinarywolf/silverstripe-treehugger)
[![Latest Stable Version](https://poser.pugx.org/silbinarywolf/silverstripe-treehugger/version.svg)](https://github.com/silbinarywolf/silverstripe-treehugger/releases)
[![Latest Unstable Version](https://poser.pugx.org/silbinarywolf/silverstripe-treehugger/v/unstable.svg)](https://packagist.org/packages/silbinarywolf/silverstripe-treehugger)
[![Total Downloads](https://poser.pugx.org/silbinarywolf/silverstripe-treehugger/downloads.svg)](https://packagist.org/packages/silbinarywolf/silverstripe-treehugger)
[![License](https://poser.pugx.org/silbinarywolf/silverstripe-treehugger/license.svg)](https://github.com/silbinarywolf/silverstripe-treehugger/blob/master/LICENSE.md)

Add additional menus (such as footer, sidebars) programmatically that can be managed by CMS users nicely on the SiteConfig or Multisite view.

## Composer Install

```
composer require silbinarywolf/silverstripe-treehugger:~2.0.0
```

## Features

- Add a checkbox to the "Settings" tab for a menu kind.
- Allow sorting of menus that is independent of the ordering in the site tree.
    - Requires [GridField Extensions](https://github.com/symbiote/silverstripe-gridfieldextensions).
- Easy to drop-in and use with partial caching.
    - Partial caching is recommended to improve page-load times when you have thousands of pages.

## Requirements

* SilverStripe 4.0+
* (Optional) SiteConfig
* (Optional) [Multisites](https://github.com/symbiote/silverstripe-multisites)
* (Optional) [GridField Extensions 2.X or 3.X](https://github.com/symbiote/silverstripe-gridfieldextensions)

**NOTE: To manage menus at the top-level of the site, you must have either SiteConfig or Multisites installed. If you want the ability to sort the menu, you need to install GridField Extensions.**

## Documentation

* [Quick Start](docs/en/quick-start.md)
* [License](LICENSE.md)
* [Contributing](CONTRIBUTING.md)

## Credits

* [Jake Bentvelzen](https://github.com/SilbinaryWolf) for the initial build
