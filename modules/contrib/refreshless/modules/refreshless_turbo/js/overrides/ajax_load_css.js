/**
 * @file
 * Drupal Ajax add CSS ('add_css') command override for RefreshLess.
 *
 * This alters core's behaviour with the following:
 *
 * - Adds a loadjs.isDefined() check before trying to call loadjs() to prevent
 *   it throwing an uncaught error which would cause this and any subsequent
 *   Ajax commands to fail. An example would be Drupal dialogs, which would
 *   completely fail to open a second time due to this.
 *
 * - Invokes our stylesheet manager to sort all stylesheets into the correct
 *   order provided by the back-end.
 *
 * - Various formatting and readability improvements.
 *
 * @see core/misc/ajax.js
 *
 * @see https://github.com/kubetail-org/loadjs
 *
 * @todo Remove this if/when we rework additive aggregation so that it doesn't
 *   have to output all CSS in spite of the page state?
 *
 * @todo Also checks for duplicates and then de-duplicate? Is this needed?
 */
((Drupal, $, loadjs) => {

  const addCss = (ajax, response, status) => {

    if (typeof response.data === 'string') {

      Drupal.deprecationError({
        message:
          'Passing a string to the Drupal.ajax.add_css() method is deprecated in 10.1.0 and is removed from drupal:11.0.0. See https://www.drupal.org/node/3154948.',
      });

      $('head').prepend(response.data);

      return;

    }

    const allUniqueBundleIds = response.data.map((bundle) => {

      const uniqueBundleId = bundle.href + ajax.instanceIndex;

      // LoadJS will throw an error if attempting to load a bundle that it's
      // already loaded.
      if (loadjs.isDefined(uniqueBundleId)) {
        return uniqueBundleId;
      }

      // Force file to load as a CSS stylesheet using 'css!' flag.
      loadjs(`css!${bundle.href}`, uniqueBundleId, {

        before(path, element) {

          // This allows all attributes to be added, like media.
          Object.keys(bundle).forEach((attributeKey) => {
            element.setAttribute(attributeKey, bundle[attributeKey]);
          });

        },

      });

      return uniqueBundleId;

    });

    $('html').trigger('refreshless:sort-stylesheets');

    // Returns the promise so that the next AJAX command waits on the
    // completion of this one to execute, ensuring the CSS is loaded before
    // executing.
    return new Promise((resolve, reject) => {

      loadjs.ready(allUniqueBundleIds, {

        success() {

          // All CSS files were loaded. Resolve the promise and let the
          // remaining commands execute.
          resolve();

        },
        error(depsNotFound) {

          const message = Drupal.t(
            `The following files could not be loaded: @dependencies`,
            { '@dependencies': depsNotFound.join(', ') },
          );

          reject(message);

        },

      });

    });

  };

  // Since this library is added as a dependency of core's, we'll be before it
  // in execution order, so we can't replace the core command when executed. As
  // a workaround, we wrap this in a one-off behaviour that only replaces the
  // command the first time and thereafter does nothing.
  Drupal.behaviors.refreshlessTurboAjaxAddCss = {

    attach(context, settings) {

      if (Drupal.AjaxCommands.prototype.add_css === addCss) {
        return;
      }

      Drupal.AjaxCommands.prototype.add_css = addCss;

    },

  };

})(Drupal, jQuery, loadjs);
