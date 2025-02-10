Most of our events are modelled after [Turbo's
events](https://turbo.hotwired.dev/reference/events).

## General events

* `refreshless:load` Triggered once on initial load and thereafter on every RefreshLess navigation load.

    * `event.detail.initial` will be `true` if this is the initial load and `false` thereafter.

* `refreshless:click` Triggered on a link when it's clicked and will be handled by RefreshLess.

    * `event.target` is the link that was clicked.

    * `event.detail.url` is a [`URL` object](https://developer.mozilla.org/en-US/docs/Web/API/URL) for the location the link points to.

    * `event.detail.originalEvent` is the original [`click` event](https://developer.mozilla.org/en-US/docs/Web/API/Element/click_event).

    * Can be cancelled via `event.preventDefault()` to allow the click to fall through to the browser and/or any other click handlers to handle, and will not result in a RefreshLess navigation.

## Render events

* `refreshless:before-render` Triggered before a new page is about to be rendered.

    * `event.detail.delay()` can be called and passed [a Promise executor function](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Promise/Promise#executor) to delay rendering; rendering will only resume once all handlers that have called `event.detail.delay()` have resolved their respective promises. An example use-case for this is to perform a transition out before changes are made to the page content.

    * `event.detail.newBody` is the new [`HTMLBodyElement`](https://developer.mozilla.org/en-US/docs/Web/API/HTMLBodyElement) that will replace the current `<body>`.

    * `event.detail.render` can be set to a custom function to render the page; see [the Turbo custom rendering documentation](https://turbo.hotwired.dev/handbook/drive#custom-rendering).

    * `event.detail.renderMethod` is the strategy that will be used to render the page; either `replace` or `morph`.

* `refreshless:render` Triggered when a new page has been rendered.

    * `event.detail.renderMethod` is the strategy used to render the page; either `replace` or `morph`.

    * Unlike the corresponding [`turbo:render` event](https://turbo.hotwired.dev/reference/events#turbo%3Arender), this is not triggered when rendering a cached preview, only when rendering the fresh version.

## Navigation events

* `refreshless:navigation-response` Triggered when a response has been received from the server that will result in a *non-redirect* RefreshLess navigation.

    * `event.detail.url` is a [`URL` object](https://developer.mozilla.org/en-US/docs/Web/API/URL) for the location that will be navigated to.

    * `event.detail.fetchResponse` is a [Turbo `FetchResponse`](https://turbo.hotwired.dev/reference/drive#fetchresponse) instance.

* `refreshless:redirect` Triggered when a response has been received from the server that will result in a *redirect* RefreshLess navigation.

    * `event.detail.from` is a [`URL` object](https://developer.mozilla.org/en-US/docs/Web/API/URL) for the location that was requested, before redirecting.

    * `event.detail.to` is a [`URL` object](https://developer.mozilla.org/en-US/docs/Web/API/URL) for the location that is being redirected to.

    * `event.detail.fetchResponse` is a [Turbo `FetchResponse`](https://turbo.hotwired.dev/reference/drive#fetchresponse) instance.

* `refreshless:reload` Triggered when RefreshLess is about to perform a hard (full) reload of the page. This is often in response to a signal from the back-end that something significant has changed that cannot be merged into the existing page.

    * `event.detail.reason` will contain the reason for the reload.

    * The following currently trigger a reload:

        * Navigating to a page that has a different Drupal theme; for example, going between the default theme and the administration theme or vice versa.

        * If CSS aggregation was not enabled on one page and then was enabled on the subsequent page, or vice versa.

        * If JavaScript aggregation was not enabled on one page and then was enabled on the subsequent page, or vice versa.

        * If the Drupal asset query string changed.

        * If the session's user permissions hash changed; for example, logging in or out, roles added or removed, and so on.

## HTTP events

* `refreshless:before-fetch-request` Triggered before a fetch request is about to be sent.

    * `event.detail.fetchOptions` is [a `RequestInit` options object](https://developer.mozilla.org/en-US/docs/Web/API/RequestInit) that will be used to construct the fetch request.

    * `event.detail.isFormSubmit` will be `true` if the request is a form submission, or `false` otherwise.

    * `event.detail.isPrefetch` will be `true` if the request is a prefetch request, or `false` otherwise.

    * `event.detail.url` is a [`URL` object](https://developer.mozilla.org/en-US/docs/Web/API/URL) for the location the fetch request will be sent to.

    * [Can be cancelled](https://turbo.hotwired.dev/handbook/drive#pausing-requests) via `event.preventDefault()`; call `event.detail.resume()` to resume sending the fetch request after you've made any changes you need to the fetch options or page changes.

* `refreshless:fetch-request-error` Triggered if a fetch request could not be completed.

    * `event.detail.fetchRequest` is the [Turbo `FetchRequest`](https://turbo.hotwired.dev/reference/drive#fetchrequest) that could not be completed.

    * `event.detail.error` is the [`Error` object](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Error) containing the reason for the failure.

Note that there is no `refreshless:before-fetch-response` implemented at this
time. Instead, one of `refreshless:navigation-response`,
`refreshless:prefetch`, or `refreshless:redirect` will be triggered depending
on the type of response received.

## Form events

* `refreshless:form-submit-start` Triggered when a form submit is about to begin.

* `refreshless:form-submit-response` Triggered when a form submit response has been received from the server.

## Prefetch events

* `refreshless:before-prefetch` Triggered before a prefetch request is about to be sent to the server.

    * `event.target` is the link that triggered the prefetch.

    * `event.detail.url` is a [`URL` object](https://developer.mozilla.org/en-US/docs/Web/API/URL) for the location to be prefetched.

    * Can be cancelled via `event.preventDefault()` to prevent the prefetch from being sent.

* `refreshless:prefetch` Triggered when a prefetch response has been received.

    * `event.detail.url` is a [`URL` object](https://developer.mozilla.org/en-US/docs/Web/API/URL) for the location that was prefetched.

    * `event.detail.fetchResponse` is a [Turbo `FetchResponse`](https://turbo.hotwired.dev/reference/drive#fetchresponse) instance.

## Behaviour events

* `refreshless:detach` Triggered when behaviours have been detached right before a RefreshLess load is about to begin.

* `refreshless:attach` Triggered when behaviours have been attached following a RefreshLess load.

## `drupalSettings` events

* `refreshless:drupal-settings-update` Triggered when `drupalSettings` has been updated due to RefreshLess navigation.

    * `event.detail.new` will contain a copy of the new settings as received in the response.

    * `event.detail.previous` will contain a copy of the old settings before merging.

    * `event.detail.merged` will contain a reference to the current, merged `drupalSettings`.

## Script events

Note that unlike stylesheets, `<script>` don't currently have remove events because removing has no effect on already-evaluated JavaScript and there's no universal way to remove its effects, unlike removing a stylesheet.

* `refreshless:before-scripts-merge` Triggered before new `<script>` elements are about to be merged. *This is triggered once for scripts in the `<head>` and a second time for scripts in the `<body>`.*

    * `event.detail.context` contains either `head` or `body`.

    * `event.detail.old` contains the `<script>` elements currently attached to the context (i.e. `head` or `body`).

    * `event.detail.new` contains the new `<script>` elements about to be merged into the context (i.e. `head` or `body`); this can be modified by event handlers to add or remove elements.

* `refreshless:scripts-merged` Triggered after new `<script>` elements have been merged. *This is triggered once for scripts in the `<head>` and a second time for scripts in the `<body>`.*

    * `event.detail.context` contains either `head` or `body`.

    * `event.detail.old` contains the `<script>` elements previously attached to the context (i.e. `head` or `body`) before the merge.

    * `event.detail.new` contains the new `<script>` elements that have been merged into the context (i.e. `head` or `body`).

* `refreshless:scripts-loaded` Triggered when all newly merged `<script>` elements have loaded. *This is triggered only once for the `<head>` and `<body>` combined.*

    * `event.detail.loaded` contains the newly merged and loaded `<script>` elements.

## Stylesheet events

Unlike scripts, stylesheets are always merged into the `<head>` and as such, these events only trigger once per page load.

* `refreshless:before-stylesheets-merge` Triggered before new stylesheets are about to be merged in.

    * `event.detail.newStylesheets` contains the new stylesheets to be merged; this can be modified by event handlers to add or remove elements.

    * `event.detail.oldStylesheets` contains the stylesheets currently attached.

* `refreshless:stylesheets-merged` Triggered after new stylesheets have been merged in.

    * `event.detail.loadingElements` is an array of [Promises](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Promise) that resolve when their respective stylesheets have loaded, or reject if they fail to load or hit their time limit.

    * `event.detail.mergedStylesheets` contains the newly merged in stylesheets.

* `refreshless:stylesheets-loaded` Triggered when all newly merged stylesheets have finished loading.

    * `event.detail.loadingElements` is the array of [Promises](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Promise) have resolved when their respective stylesheets have loaded.

* `refreshless:before-stylesheets-remove` Triggered before stylesheets are about to be removed.

    * `event.detail.stylesheets` contains the stylesheets to be removed; this can be modified by event handlers to add or remove elements.

* `refreshless:stylesheets-removed` Triggered after stylesheets have been removed.

    * `event.detail.stylesheets` contains the stylesheets that were removed.

## Progress bar events

* `refreshless:progress-bar-before-show` Triggered when the progress bar is about to be shown and start trickling; can be cancelled to prevent showing it.

* `refreshless:progress-bar-shown` Triggered when the progress bar has been shown.

* `refreshless:progress-bar-before-hide` Triggered when the progress bar is about to start hiding; can be cancelled to keep it visible and keep it trickling.

* `refreshless:progress-bar-hidden` Triggered when the progress bar has finished transitioning out and is fully hidden.
