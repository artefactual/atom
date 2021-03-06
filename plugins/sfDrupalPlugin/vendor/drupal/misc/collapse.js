// $Id: collapse.js,v 1.27 2009/11/08 11:46:22 webchick Exp $
(function ($) {

/**
 * Toggle the visibility of a fieldset using smooth animations.
 */
Drupal.toggleFieldset = function (fieldset) {
  if ($(fieldset).is('.collapsed')) {
    // Action div containers are processed separately because of a IE bug
    // that alters the default submit button behavior.
    var content = $('> div:not(.action)', fieldset);
    $(fieldset)
      .removeClass('collapsed')
      .trigger({ type: 'collapsed', value: false })
      .find('> legend > a > span.element-invisible')
        .empty()
        .append(Drupal.t('Hide'));
    content.hide();
    content.slideDown({
      duration: 'fast',
      easing: 'linear',
      complete: function () {
        Drupal.collapseScrollIntoView(this.parentNode);
        this.parentNode.animating = false;
        $('div.action', fieldset).show();
      },
      step: function () {
        // Scroll the fieldset into view.
        Drupal.collapseScrollIntoView(this.parentNode);
      }
    });
  }
  else {
    $('div.action', fieldset).hide();
    $(fieldset).trigger({ type: 'collapsed', value: true });
    var content = $('> div:not(.action)', fieldset).slideUp('fast', function () {
      $(this.parentNode).addClass('collapsed')
        .find('> legend > a > span.element-invisible')
          .empty()
          .append(Drupal.t('Show'));
      this.parentNode.animating = false;
    });
  }
};

/**
 * Scroll a given fieldset into view as much as possible.
 */
Drupal.collapseScrollIntoView = function (node) {
  var h = self.innerHeight || document.documentElement.clientHeight || $('body')[0].clientHeight || 0;
  var offset = self.pageYOffset || document.documentElement.scrollTop || $('body')[0].scrollTop || 0;
  var posY = $(node).offset().top;
  var fudge = 55;
  if (posY + node.offsetHeight + fudge > h + offset) {
    if (node.offsetHeight > h) {
      window.scrollTo(0, posY);
    }
    else {
      window.scrollTo(0, posY + node.offsetHeight - h + fudge);
    }
  }
};

Drupal.behaviors.collapse = {
  attach: function (context, settings) {
    $(location.hash, context).removeClass('collapsed');

    $('fieldset.collapsible > legend', context).each(function () {
      var fieldset = $(this.parentNode);
      // Expand if there are errors inside.
      if ($('input.error, textarea.error, select.error', fieldset).length > 0) {
        fieldset.removeClass('collapsed');
      }

      var summary = $('<span class="summary"></span>');
      fieldset.
        bind('summaryUpdated', function () {
          var text = $.trim(fieldset.getSummary());
          summary.html(text ? ' (' + text + ')' : '');
        })
        .trigger('summaryUpdated');

      // Turn the legend into a clickable link and wrap the contents of the
      // fieldset in a div for easier animation.
      var text = this.innerHTML;
      $(this).empty()
        .append($('<a href="#">' + text + '</a>')
          .click(function () {
            var fieldset = $(this).parents('fieldset:first')[0];
            // Don't animate multiple times.
            if (!fieldset.animating) {
              fieldset.animating = true;
              Drupal.toggleFieldset(fieldset);
            }
            return false;
          })
          .prepend($('<span class="element-invisible"></span>')
            .append(fieldset.hasClass('collapsed') ? Drupal.t('Show') : Drupal.t('Hide'))
            .after(' ')
          )
        )
        .append(summary)
        .after(
          $('<div class="fieldset-wrapper"></div>')
            .append(fieldset.children(':not(legend):not(.action)'))
        );
    });
  }
};

})(jQuery);
