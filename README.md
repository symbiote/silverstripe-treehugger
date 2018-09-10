# TreeHugger (Sortable Menus)

[![Build Status](https://travis-ci.org/symbiote/silverstripe-treehugger.svg?branch=master)](https://travis-ci.org/symbiote/silverstripe-treehugger)
[![Latest Stable Version](https://poser.pugx.org/symbiote/silverstripe-treehugger/version.svg)](https://github.com/symbiote/silverstripe-treehugger/releases)
[![Latest Unstable Version](https://poser.pugx.org/symbiote/silverstripe-treehugger/v/unstable.svg)](https://packagist.org/packages/symbiote/silverstripe-treehugger)
[![Total Downloads](https://poser.pugx.org/symbiote/silverstripe-treehugger/downloads.svg)](https://packagist.org/packages/symbiote/silverstripe-treehugger)
[![License](https://poser.pugx.org/symbiote/silverstripe-treehugger/license.svg)](https://github.com/symbiote/silverstripe-treehugger/blob/master/LICENSE.md)

Add additional menus (such as footer, sidebars) programmatically that can be managed by CMS users nicely on the SiteConfig or Multisite view.

## Composer Install

```
composer require symbiote/silverstripe-treehugger:~3.0
```

## Features

- Page CMS editing, Adds a checkbox to the "Settings" tab for each defined menu kind.
- Allow re-ordering of pages for each menu kind (independent of the ordering in the site tree)
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
