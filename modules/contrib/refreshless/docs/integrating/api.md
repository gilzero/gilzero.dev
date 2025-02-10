## Methods

* `Drupal.RefreshLess.visit(location, options)` Can be called to perform a RefreshLess visit to a location if possible. See [the documentation for `Turbo.visit()`](https://turbo.hotwired.dev/reference/drive#turbo.visit) for details on parameters. Unlike `Turbo.visit()`, our method will check [if Turbo Drive is enabled](https://turbo.hotwired.dev/handbook/drive#disabling-turbo-drive-on-specific-links-or-forms), and will perform a full page load if it isn't.

## Events

In addition to the [events](events.md) you can listen to, it's also possible to
trigger the following to invoke RefreshLess to perform actions:

* `refreshless:sort-stylesheets` Can be triggered to cause RefreshLess to sort stylesheets currently attached to the document into the correct order as specified by the back-end. This is used when merging stylesheets to preserve CSS specificity, and is usually done automatically while merging and after Ajax requests.
