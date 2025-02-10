# RefreshLess implementation using the [Hotwire Turbo library](https://turbo.hotwired.dev/)

## Requirements

* [Drupal core](https://www.drupal.org/project/drupal) 10.3 or newer

* [PHP](https://www.php.net/) 8.1 or newer

* [Composer](https://getcomposer.org/)

* [`cweagans/composer-patches` 1.x](https://github.com/cweagans/composer-patches/tree/1.x)

We generally recommend [DDEV](https://ddev.com/) because it provides most or all of the above for you out of the box, and [helps you get a Drupal project up and running](https://www.drupal.org/docs/develop/local-server-setup/docker-based-development-environments-for-macos-linux-and-windows) much faster while also isolating it from the rest of your operating system via containerization.

## Installation

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

## Known issues

* JavaScript aggregation works but there are limitations and steps required to enable it:

  * We force all JavaScript into the `<head>` but add the `defer` attribute as [recommended by the Turbo documentation](https://turbo.hotwired.dev/handbook/building#loading-your-application%E2%80%99s-javascript-bundle); this is necessary to prevent Turbo re-evaluating `<script>` elements that it finds in the `<body>`; [core is patched to allow aggregating JavaScript with the `defer` attribute](https://www.drupal.org/project/drupal/issues/1587536).

  * [We alter the core JavaScript aggregation behaviour to be additive during Turbo navigation](https://www.drupal.org/project/refreshless/issues/3414538), tracking what libraries are already loaded on a page in a similar way to Drupal's Ajax system. Because this can have multiple variations per page, Drupal's anonymous page caching is disabled for Turbo requests, though the dynamic page cache and render cache are unaffected.

  * Turbo itself is not aggregated/minified at the moment as it starts to behave unpredictably and even attach more than once to a page if aggregated.

## Development

### Requirements

In addition to the main requirements above, the following is required to update
Turbo and patch it:

* A front-end package manager that supports both [the Plug'n'Play install strategy](https://yarnpkg.com/features/pnp) and [workspaces](https://yarnpkg.com/features/workspaces):

  * [Yarn 3 or newer](https://yarnpkg.com/getting-started/install) (Yarn 1 *will not work*; Yarn 2 *might work* but has not been tested)

  * [pnpm](https://pnpm.io/) may also work but has not been tested

### Installation

The instructions here are for [Yarn](https://yarnpkg.com/), but can be adapted
for [pnpm](https://pnpm.io/) or other package managers.

The following assumes you're using [Yarn
workspaces](https://yarnpkg.com/features/workspaces) from the root of your
Drupal installation, i.e. where your root `composer.json` is located, one level
above the web directory. You must also be using [the Plug'n'Play install
strategy](https://yarnpkg.com/features/pnp), which is the default in modern
Yarn. You'll need to have a `package.json` in the root directory containing a
`workspaces` array:

```json
{
  "workspaces": [
    "web/modules/contrib/refreshless/**!(vendor)/*"
  ]
}
```

If you already have other workspace paths defined, just merge that in.

Then, run `yarn add drupal-refreshless-turbo@workspace:^2` and Turbo will be
downloaded and extracted to the correct location. This is accomplished via the
[`@consensus.enterprises/pnp-vendorize`](https://www.npmjs.com/package/@consensus.enterprises/pnp-vendorize)
package.

### Patching

The following describes how to apply our Turbo patches [via
Yarn](https://yarnpkg.com/features/patching) but can also be [adapted for
pnpm](https://pnpm.io/cli/patch). We may eventually contribute to Turbo and
remove the need for patches, but until that time, patching will be required to fix various issues. *Note that this has only been tested on Linux, may work on
macOS, and will likely fail on Windows.* The following scripts are relevant:

- `yarn workspace drupal-refreshless-turbo vendorize` - This will extract an unaltered (unpatched) copy of Turbo.

- `yarn workspace drupal-refreshless-turbo patch-turbo` - This will apply all our patches to Turbo.

### Patches

#### Turbo

* Removal of stylesheets marked as `data-turbo-track="dynamic"` is not delayed when rendering is delayed via `turbo:before-render` (Turbo issue TBD); patch is [`patches/@hotwired/turbo/01-delay-stylesheet-remove-with-render.patch`](modules/refreshless_turbo/patches/@hotwired/turbo/01-delay-stylesheet-remove-with-render.patch).

* [Visiting pages with 4xx response codes causes re-evaluation of `<head>` JavaScript](https://www.drupal.org/project/refreshless/issues/3422964); patch is [`patches/@hotwired/turbo/02-issue-1190-render-error-script-eval.patch`](modules/refreshless_turbo/patches/@hotwired/turbo/02-issue-1190-render-error-script-eval.patch).

* [Firefox scrolling to in-page anchor is instant and abrupt with smooth scrolling enabled](https://github.com/hotwired/turbo/issues/1255); patch is [`patches/@hotwired/turbo/03-issue-1255-smooth-scroll-hash.patch`](modules/refreshless_turbo/patches/@hotwired/turbo/03-issue-1255-smooth-scroll-hash.patch).

* [In-page anchors and `hashchange` events can cause full page visits or otherwise behave unexpectedly](https://www.drupal.org/project/refreshless/issues/3416085); patch is [`patches/@hotwired/turbo/04-pull-1125-anchor-hashchange.patch`](modules/refreshless_turbo/patches/@hotwired/turbo/04-pull-1125-anchor-hashchange.patch).

* Progress bar has been completely removed in favour of our own; patch is [`patches/@hotwired/turbo/05-remove-progress-bar.patch.patch`](modules/refreshless_turbo/patches/@hotwired/turbo/05-remove-progress-bar.patch.patch).
