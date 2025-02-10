# RefreshLess implementation using Drupal's Ajax framework

RefreshLess makes navigating your web site faster by only loading the parts that change between pages. Uses the ideas behind [Turbolinks technique](https://github.com/turbolinks/turbolinks) (pioneered by Rails). But it goes further: thanks to Drupal 8's architecture, we're able to automatically figure out which parts of a page change. So, we only need to send the parts of the page that actually change.

When clicking a link that points to a URL within the Drupal site, we then *keep* the current page, but just swap the parts that change between the current page and the next. This means less data on the wire, and — most importantly — less work for the browser.

![Demo video](https://www.drupal.org/files/issues/RefreshLess%20quick%20demo.mp4)

## How does this work?

1. During rendering, we annotate the regions on the page so JavaScript can find them reliably. For now, RefreshLess works at the region level (and not the block level) to ensure it works with *any* page, no matter whether it uses Blocks, Panels or Page Manager.

2. Drupal 8 already has all [the necessary cacheability metadata](https://www.drupal.org/developing/api/8/cache), and in particular [cache contexts](https://www.drupal.org/developing/api/8/cache/contexts), which describe how each part of the page varies.

3. The RefreshLess module uses the cache contexts associated with a particular region to determine whether this region changes compared to the page the user is currently on. It only sends the updated markup for that region if it actually changes.

4. This results in hugely improved front-end/perceived performance.

So, unlike Turbolinks in Rails, using RefreshLess requires *zero* code and configuration.

Combined with the [BigPipe](https://www.drupal.org/documentation/modules/big_pipe) module (which offers significant improvements for the initial page load), this should make just about any Drupal site feel significantly faster.

**Don't forget to apply the included core patch! We're working on making this unnecessary, but for now it is required. See #2708619.**

## How to apply the patch to core

### Via Composer (recommended)

- Install composer patches plugin

  `composer require cweagans/composer-patches:^1.7`

- Add RefreshLess patch file to Composer

  `composer config extra.patches --json '{"drupal/core": {"RefreshLess": "web/modules/contrib/refreshless/modules/refreshless_ajax/patches/drupal/core/refreshless-2708619.patch"}}'`

### Manually via Git

- `git apply --directory=web -v web/modules/contrib/refreshless/modules/refreshless_ajax/patches/drupal/core/refreshless-2708619.patch`

## TODO

- Fix Drupal core, so that a core patch is no longer necessary.
- Test coverage
- The contextual links' `?destination=` query argument does not get updated.

## Disabling RefreshLess on specific links

RefreshLess can be disabled on a per-link basis by setting the
`data-refreshless-exclude` attribute on it:

```html
<a href="/somewhere" data-refreshless-exclude>Ignored by RefreshLess</a>
```

Links with RefreshLess enabled will be handled normally by the browser.

## Events

- `refreshless:load` dispatched on `window` whenever a new page has
loaded through RefreshLess

## API

- `Drupal.RefreshLess.visit(url)`

## How RefreshLess uses the History API

- Every URL transition within Drupal is tracked: both inter-page navigation
and intra-page (fragment) navigation. See the `State` object.
- Every `State` object has a position.
- The current position in the history stack is tracked. This allows us to
detect whether the user is navigating backward or forward.
- Scroll restoration is handled entirely by History API, hence we don't
need to track the scroll position at all.

## Architecture

- Classes: Url (n), State (n), HistoryNavigation (1), LinkNavigation (1),
Controller (1)
- State uses Url
- HistoryNavigation uses State
- LinkNavigation only uses the public API
- Controller uses HistoryBasedNavigation, LinkNavigation and State

## Requirements

- Theme always has the same layout (e.g. no conditional `<body>` classes
based on current path/route).
- Theme always has the same set of regions.

Or it is at least configured to always the same layout and regions.

Example: Bartik can be problematic, but it usually is not, because it seldomly
is configured to use the flexibility it provides. As long as you just always
use the first sidebar and there always is some block visible in there, there
is no problem. See `bartik_preprocess_html().
