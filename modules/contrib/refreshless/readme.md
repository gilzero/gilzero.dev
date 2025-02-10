![Stylized "RefreshLess"](assets/optimized/title.svg)

*RefreshLess* layers JavaScript-based navigation on top of Drupal's existing
server-rendered HTML to provide the kind of smooth, fast, and responsive
experience users expect. By fully embracing [progressive
enhancement](https://en.wikipedia.org/wiki/Progressive_enhancement), you don't
need to choose between a web site/web app that continues to work if JavaScript
breaks **or** something that feels like a [single page app
(SPA)](https://en.wikipedia.org/wiki/Single-page_application) user experience -
you can have both.

Compared to an SPA:

* Provides much higher [resiliency](https://resilientwebdesign.com/) in unexpected or adverse network or runtime conditions where JavaScript fails to load or execute correctly. In such situations, your users still have a functioning site/app that falls back to traditional page loads. [The browser is the most hostile runtime environment](https://molily.de/robust-javascript/#the-browser-as-a-runtime-environment) after all.

* Reuses Drupal's server-rendered HTML, saving you the development time and cost of re-implementing a whole new front-end, while making use of Drupal's Twig templates, asset library system, caching, security, and so on.

Compared to a plain server-rendered HTML site:

* Navigation feels faster and more fluid because the browser no longer needs to re-initialize all CSS and JavaScript on every page request.

* Page transitions (with or without [the View Transitions API](https://developer.mozilla.org/en-US/docs/Web/API/View_Transitions_API)), [morphing](https://github.com/bigskysoftware/idiomorph), and [permanent/persistent elements](https://turbo.hotwired.dev/handbook/building#persisting-elements-across-page-loads) (think media players, etc.) are now possible.

* Everyone thinks you're super cool.

# Installation

Before attempting to install this, we strongly recommend enabling dependencies
to patch Drupal core; see [Known issues](#known-issues) for what we patch. Run
the following to set that up:

```shell
composer require 'cweagans/composer-patches:^1.7'
composer config extra.enable-patching true
composer config --json --merge extra.patchLevel '{"drupal/core": "-p2"}'
```

Then just `composer require` this module and core should be patched; if patches were not attempted, you can often try running `composer install` to give it a nudge to do so.

Finally, install this module in Drupal and it should take over handling page
requests.

# Hotwire Turbo implementation

RefreshLess implementation built on the [Hotwire
Turbo](https://turbo.hotwired.dev/) library. See
[`modules/refreshless_turbo/readme.md`](modules/refreshless_turbo/readme.md) for important installation instructions.
This is the current implementation but [other
implementations](https://www.drupal.org/project/refreshless/issues/3396176) are
planned.

# Classic Drupal Ajax framework implementation

This is the classic RefreshLess implementation using Drupal's Ajax framework
from the 1.x branch created by [Wim Leers](https://www.drupal.org/u/wim-leers)
and [contributors](https://www.drupal.org/node/2693129/committers). See
[`modules/refreshless_ajax/readme.md`](modules/refreshless_ajax/readme.md).

*Note that this is not maintained, not guaranteed to continue functioning at
this time, and planned to be removed as all development is focused on the
Hotwire Turbo implementation.*

# Guidelines

* Writing your JavaScript behaviours:

  * They must have detach callbacks that fully clean up changes the attach callbacks make, restoring the elements as if the attach was never applied.

  * Don't store references to elements outside of behaviour callbacks. They can and will get out of sync with the DOM, resulting in references to elements no longer on the page.

* Test your sites with *and without* RefreshLess. All necessary functionality your users need must not require RefreshLess or even JavaScript - it doesn't have to be pretty, but an ugly form is still far better than not being able to sign in to a site, pay bills, or send a message.

# Known issues

* This module's JavaScript must be grouped into a separate aggregate group to prevent it potentially being evaluated more than once if our additive aggregation doesn't behave as expected; [core is patched to allow explicitly defining an aggregation group](https://www.drupal.org/project/drupal/issues/3232810).

* The new Drupal core Navigation module does not re-attach its displacement when navigating around the admin section; [we provide a core patch to correctly detach and attach repeatedly](https://www.drupal.org/project/drupal/issues/3487905).

# Credits

Special thanks to [Wim Leers](https://www.drupal.org/u/wim-leers) and
[contributors](https://www.drupal.org/node/2693129/committers) on the 1.x
version of this module, which inspired the 2.x rework and provided a foundation
to build on.

## Branding

Logo and title created with [Inkscape](https://inkscape.org/) with the [Rubik
font](https://www.fontsquirrel.com/fonts/rubik).
