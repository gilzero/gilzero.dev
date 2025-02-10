((Drupal, $, once) => {

  'use strict';

  /**
   * The once() identifier for our behaviour.
   *
   * @type {String}
   */
  const onceName = 'refreshless-turbo-disable-smooth-scroll';

  /**
   * Class added to the <html> element during a load to prevent smooth scroll.
   *
   * This works around a Firefox issue where the browser will sometimes not
   * scroll to the top as expected on an advance visit (i.e. clicking a link,
   * not a back or forward history navigation) if scroll-behavior: smooth; is
   * set on the <html> element. The solution is to temporarily force
   * scroll-behavior: auto; (instant scrolling) right as the visit starts and
   * remove it once the load has occurred.
   *
   * Also note that Turbo at the time of writing seems to add aria-busy to
   * <html> on clicking an in-page anchor (such as a table of contents) and not
   * remove it until navigated to a different page. Because of this, we can't
   * use a CSS-only solution.
   *
   * @type {String}
   *
   * @see https://github.com/hotwired/turbo/issues/190#issuecomment-1525135845
   *   CSS-only solution for future reference.
   *
   * @see https://github.com/hotwired/turbo/issues/426
   *
   * @see https://github.com/hotwired/turbo/issues/698
   */
  const smoothScrollDisableClass = 'refreshless-disable-smooth-scroll';

  const turboLoadHandler = (event) => {
    $(event.target).removeClass(smoothScrollDisableClass);
  };

  const turboVisitHandler = (event) => {

    $(event.target)
    .addClass(smoothScrollDisableClass)
    .one('refreshless:load', turboLoadHandler);

  };

  Drupal.behaviors.refreshlessTurboDisableSmoothScroll = {

    attach(context, settings) {

      $(once(onceName, 'html', context)).on('turbo:visit', turboVisitHandler);

    },
    detach(context, settings, trigger) {

      if (trigger !== 'unload') {
        return;
      }

      $(once.remove(onceName, 'html', context))
      .off({
        'refreshless:load': turboLoadHandler,
        'turbo:visit':      turboVisitHandler,
      })
      .removeClass(smoothScrollDisableClass);

    },

  };

})(Drupal, jQuery, once);
