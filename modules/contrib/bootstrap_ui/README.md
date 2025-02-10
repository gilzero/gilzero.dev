INTRODUCTION
------------

This module integrates Bootstrap CSS framework along with a user interface
for configuring and customize components, variables and plugins.
- https://getbootstrap.com/


FEATURES
--------

This module aims to provide various settings:

  - Support all stable versions

  - Load via CDN method

  - Load from local libraries (Support compiled & source files)

  - RTL support for all versions (Such as Persian, Farsi & Arabic languages)

  - Composer install (Separated module and library)

  - Restricts load by theme and URL

  - Ability to set minified or non-minified library

  - Restrict JavaScript and CSS files to be loaded

  - More possibilities in the future

  - Customizable

  - Easy to use


REQUIREMENTS
------------

Download Bootstrap Library Desired Version from GitHub:

  - https://github.com/twbs/bootstrap/tags


INSTALLATION
------------

1. Download 'Bootstrap UI' module - https://www.drupal.org/project/bootstrap_ui


2. Extract and place it in the root of contributed modules directory i.e.
   * /modules/contrib/bootstrap_ui


3. Create a libraries directory in the root, if not already there i.e.
   * /libraries


4. Create a 'bootstrap' directory inside it i.e.
   * /libraries/bootstrap


5. Download 'Bootstrap framework' Desired Version:
   * https://github.com/twbs/bootstrap/tags


6. Place it in the /libraries/bootstrap directory i.e. Required files,

   The path of the downloaded **`distribution`** files (compiled CSS and JS)
   should be as follows:
   - /libraries/bootstrap/css/bootstrap.min.css
   - /libraries/bootstrap/js/bootstrap.min.js

   The path of the downloaded  **`source code`** files should be as follows:
   - /libraries/bootstrap/dist/css/bootstrap.min.css
   - /libraries/bootstrap/dist/js/bootstrap.min.js


    * NOTE: Use Source code (i.e. Source code tar.gz or zip)
    for advanced customization, theming, and if you need to modify
    Bootstrap at the source level. Otherwise, it's recommended
    to use the dist version (i.e. bootstrap-5.x.x-dist.zip)
    for quick setup, minimal configuration and production-ready files.


7. Finally, enable 'Bootstrap UI' module.


USAGE
=====

### Introduction to Using the Bootstrap CSS Module for Drupal

This module is designed to simplify and enhance the experience of using
Bootstrap with Drupal sites. By using this module, you can easily take
advantage of all Bootstrap versions and have a variety of management
settings at your disposal. Some of the key features of this module include:

- **Support for All Versions:** This module supports both the Distribution
    and Source code versions of Bootstrap.
- **Loading Options:** If you prefer not to download and locally load the
    Bootstrap library, you can utilize the CDN option.
- **Development Mode:** For developers and debugging purposes, the module
    allows the use of non-minimized (Development) versions in its settings.
- **Custom Settings:** You can configure restrictions for loading Bootstrap
    files, specify the themes used, and set page paths according to your needs.
- **RTL Support:** To support right-to-left languages such as Persian and
    Arabic, ensure that one of these languages is enabled on your site.


How does it Work?
-----------------

1. Enable "Bootstrap UI" module, Follow INSTALLATION in above.

2. Go to Bootstrap UI settings:
   * /admin/config/user-interface/bootstrap

3. You can change the default settings for your intended use, for example:
   * Enable RTL support or restrict load on specific themes

4. Enjoy that.

This module helps you seamlessly and flexibly integrate the popular
Bootstrap framework into your Drupal sites.

Documentation
-------------
https://getbootstrap.com/docs/5.3/getting-started/introduction/

MAINTAINERS
-----------

Current module maintainer:

 * Mahyar Sabeti - https://www.drupal.org/u/mahyarsbt
