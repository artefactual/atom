var Qubit = Qubit || {};

// Usage: log('inside coolFunc',this,arguments);
// http://paulirish.com/2009/log-a-lightweight-wrapper-for-consolelog/
window.log = function()
  {
    log.history = log.history || [];
    log.history.push(arguments);

    if (this.console)
    {
      console.log( Array.prototype.slice.call(arguments) );
    }
  };

// jQuery expander
Drupal.behaviors.expander = {
  attach: function (context)
    {
      jQuery('div.field:not(:has(div.field)) > div, article.search-result .scope-and-content, article.search-result .history').each(function (index, element) {
        var $element = jQuery(element);
        // Don't apply expander to fields with only one child, if that child is a list
        if ($element.children().length !== 1 || !$element.children().first().is('ul')) {
          $element.expander({
            slicePoint: 255,
            expandText: '&raquo;',
            expandPrefix: '... ',
            userCollapseText: '&laquo;',
            widow: 4,
            expandEffect: 'show'
          });

          (function ($) {
            'use strict';
            // Get i18n text for read more/less links from footer
            var $i18n = $('#js-i18n #read-more-less-links');

            // Get read more/less link elements
            var $readMoreLink = $element.find('.read-more a');
            var $readLessLink = $element.find('.read-less a');

            $(function() {
              // Add accessibility label to read more link
              $readMoreLink.attr('aria-label', $i18n.data('read-more-text'));

              // Add accessibility label to read less link
              $readLessLink.attr('aria-label', $i18n.data('read-less-text'));
            });

          })(jQuery);

        }
      });
    }
  };
