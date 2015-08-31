(function ($)
  {
    // Toggle checkbox with extra accounting of visual state
    var checkPermission = function (node, check) {
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
      if (typeof check === 'undefined') {
        check = !$li.hasClass('cbx-checked');
      }

      // Update the checkbox and its <li/> container
      $li.toggleClass('cbx-checked', check);
      $input.prop('checked', check).attr('checked', check);
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
          if (event.target.tagName === 'LI') {
            checkPermission(event.target);
          }
        })
        .on('change', function (event) {
          checkPermission(event.target, event.target.checked);
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

      $('input[name=preview]').click(function (e)
        {
          e.preventDefault();

          var $form = $(this).closest('form').attr('target', '_blank');
          var $input = $('<input/>').attr({'type': 'hidden', 'name': 'preview', 'value': 'true' }).appendTo($form);

          $form.submit();
          $form.removeAttr('target');
          $input.remove();
        });

      var $premisAccessStatementsArea = $('#premisAccessStatementsArea');
      $premisAccessStatementsArea.find('.nav-tabs li:first-child').addClass('active');
      $premisAccessStatementsArea.find('.tab-content .tab-pane:first-child').addClass('active');

    });
  }
)(jQuery);
