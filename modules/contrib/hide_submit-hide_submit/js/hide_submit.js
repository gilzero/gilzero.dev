/**
 * @file
 * A JavaScript file for the theme.
 * This file should be used as a template for your other js files.
 * It defines a drupal behavior the "Drupal way".
 *
 */

// JavaScript should be made compatible with libraries other than jQuery by
// wrapping it with an "anonymous closure". See:
// - https://drupal.org/node/1446420
// - http://www.adequatelygood.com/2010/3/JavaScript-Module-Pattern-In-Depth

(function ($, Drupal, drupalSettings, window, document, undefined) {
  'use strict';

  // To understand behaviors, see https://drupal.org/node/756722#behaviors
  Drupal.behaviors.hideSubmitBlockit = {
    attach: function(context) {
      var timeoutId = null;

      once('hide-submit-button', 'form', context).forEach((form) => {
        var $form = $(form);

        // Bind to input elements.
        if (drupalSettings.hide_submit.method === 'indicator') {
          // Replace input elements with buttons.
          $('input.form-submit', $form).each(function(index, el) {
            var attrs = {};

            $.each($(this)[0].attributes, function(idx, attr) {
                attrs[attr.nodeName] = attr.nodeValue;
            });

            $(this).replaceWith(function() {
                return $("<button/>", attrs).append($(this).attr('value'));
            });
          });
          // Add needed attributes to the submit buttons.
          $('button.form-submit', $form).each(function(index, el) {
            $(this).addClass('ladda-button button').attr({
              'data-style': drupalSettings.hide_submit.indicator_style,
              'data-spinner-color': drupalSettings.hide_submit.spinner_color,
              'data-spinner-lines': drupalSettings.hide_submit.spinner_lines
            });
          });
          Ladda.bind('.ladda-button', $form, {
            timeout: drupalSettings.hide_submit.reset_time
          });
        }
        else {
          $('input.form-submit, button.form-submit', $form).click(function (e) {
            var el = $(this);
            el.after('<input type="hidden" name="' + el.attr('name') + '" value="' + el.attr('value') + '" />');
            return true;
          });
        }

        // Bind to form submit.
        $('form', context).submit(function (e) {
          var $inp;
          if (!e.isPropagationStopped()) {
            if (drupalSettings.hide_submit.method === 'disable') {
              $('input.form-submit, button.form-submit', $form).attr('disabled', 'disabled').each(function (i) {
                var $button = $(this);
                if (drupalSettings.hide_submit.hide_submit_css) {
                  $button.addClass(drupalSettings.hide_submit.hide_submit_css);
                }
                if (drupalSettings.hide_submit.abtext) {
                  $button.val($button.val() + ' ' + drupalSettings.hide_submit.abtext);
                }
                $inp = $button;
              });

              if ($inp && drupalSettings.hide_submit.atext) {
                $inp.after('<span class="hide-submit-text">' + Drupal.checkPlain(drupalSettings.hide_submit.atext) + '</span>');
              }
            }
            else if (drupalSettings.hide_submit.method === 'hide'){
              var pdiv = '<div class="hide-submit-text">' + Drupal.checkPlain(drupalSettings.hide_submit.hide_text) + '</div>';
              if (drupalSettings.hide_submit.hide_fx) {
                $('input.form-submit, button.form-submit', $form).fadeOut(100).eq(0).after(pdiv);
                $('input.form-submit, button.form-submit', $form).next().fadeIn(100);
              }
              else {
                $('input.form-submit, button.form-submit', $form).hide().eq(0).after(pdiv);
              }
            }
            // Add a timeout to reset the buttons (if needed).
            if (drupalSettings.hide_submit.reset_time) {
              timeoutId = window.setTimeout(function() {
                hideSubmitResetButtons(null, $form);
              }, drupalSettings.hide_submit.reset_time);
            }
          }
          return true;
        });
      });

      // Bind to clientsideValidationFormHasErrors to support clientside validation.
      // $(document).bind('clientsideValidationFormHasErrors', function(event, form) {
        //hideSubmitResetButtons(event, form.form);
      // });

      // Reset all buttons.
      function hideSubmitResetButtons(event, form) {
        // Clear timer.
        window.clearTimeout(timeoutId);
        timeoutId = null;
        switch (drupalSettings.hide_submit.method) {
          case 'disable':
            $('input.' + Drupal.checkPlain(drupalSettings.hide_submit.hide_submit_css) + ', button.' + Drupal.checkPlain(drupalSettings.hide_submit.hide_submit_css), form)
              .each(function (i, el) {
                $(el).removeClass(Drupal.checkPlain(drupalSettings.hide_submit.hide_submit_hide_css))
                  .removeAttr('disabled');
              });
            $('.hide-submit-text', form).remove();
            break;

          case 'indicator':
            Ladda.stopAll();
            break;

          default:
            $('input.' + Drupal.checkPlain(drupalSettings.hide_submit.hide_submit_css) + ', button.' + Drupal.checkPlain(drupalSettings.hide_submit.hide_submit_css), form)
              .each(function (i, el) {
                $(el).stop()
                  .removeClass(Drupal.checkPlain(drupalSettings.hide_submit.hide_submit_hide_css))
                  .show();
              });
            $('.hide-submit-text', form).remove();
        }
      }
    }
  };

})(jQuery, Drupal, drupalSettings, window, this.document);
