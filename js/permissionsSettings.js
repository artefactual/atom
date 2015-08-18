(function ($)
  {
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
          }
        });

      $area.find('.all').on('click', function (event) {
        event.preventDefault();
        $area.find('input').prop('checked', true);
      });

      $area.find('.none').on('click', function (event) {
        event.preventDefault();
        $area.find('input').prop('checked', false);
      });

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

        $cbs.prop('checked', !checked);

      });

    });
  }
)(jQuery);
