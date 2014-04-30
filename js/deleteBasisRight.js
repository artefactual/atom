Drupal.behaviors.deleteBasisRightAction = {
  attach: function(context)
  {
    jQuery('#content').on('click', '.deleteRightBasis', function(){
      var $this = jQuery(this);
      var href = $this.attr('data-href');

      var success = function(){
        $this.parent().remove();
      }
      
      jQuery.get(href, success($this));

    });
  }
}