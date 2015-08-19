(function ($)
  {
    // Toggle checkbox with extra accounting of visual state
    var checkPermission = function (node, checked) {
      var $li, $input;
      switch (node.tagName) {
        case 'INPUT':
          $input = $(node);
          $li = $input.parent();
          break;
        case 'LI':
          $li = $(node);
          $input = $li.find('input');
          break;
        default:
          return false;
      }

      // If the desired state is not given we figure it out based on the
      // previous value
      if (typeof checked === 'undefined') {
        checked = !$li.hasClass('cbx-checked');
      }

      // Update the checkbox and its <li/> container
      $li.toggleClass('cbx-checked', checked);
      $input.prop('checked', checked);
    };

    // Convenience wrapper in jQuery
    $.fn.checkPermission = function(checked) {
      this.each(function () {
        checkPermission.apply(null, [this, checked]);
      });
    };

    $(document).ready(function() {

      var $area = $('#premisAccessPermissionsArea');

      $area.find('.cbx')
        .hover(function () {
          $(this).addClass('hover');
        }, function () {
          $(this).removeClass('hover');
        })
        .on('click', function (event) {
          if (event.target.tagName !== 'INPUT') {
            $(event.target).find('input').trigger('click');
          } else {
            event.preventDefault(); // We handle this with our checkPermission fn
            checkPermission(event.target);
          }
        });

      $area.find('.all').on('click', function (event) {
        event.preventDefault();
        $area.find('input').checkPermission(true);
      });

      $area.find('.none').on('click', function (event) {
        event.preventDefault();
        $area.find('input').checkPermission(false);
      });

      $area.find('.cbx:has(input:checked)').addClass('cbx-checked');

      $area.find('.btn-check-col').on('click', function (event) {

        event.preventDefault();

        // Find index of the column being clicked
        var $li = $(this.parentNode);
        var index;
        $area.find('th.premis-permissions-mrt li').each(function (i) {
          if ($li.is(this)) {
            index = i;
            return false;
          }
        });

        // Create a jQuery object with all the checkboxes of the column in it
        var $cbs = jQuery();
        $area.find('tbody tr').each(function () {
          var $input = $(this).find('input').eq(index);
          $cbs = $cbs.add($input);
        });

        // Are they all checked?
        var checked = true;
        $cbs.each(function() {
          if (!$(this).prop('checked')) {
            checked = false;
            return false;
          }
        });

        $cbs.checkPermission(!checked);

      });

    });
  }
)(jQuery);
