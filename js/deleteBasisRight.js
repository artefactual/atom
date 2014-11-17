Drupal.behaviors.deleteBasisRightAction = {
  attach: function(context)
  {
    jQuery('#content').on('click', '.deleteRightBasis', function(event){
      event.preventDefault();

      var $this = jQuery(this);
      var href = $this.attr('href');

      var success = function(){
        $this.parent().slideUp();
      }

      jQuery.get(href, success($this));

      return false;
    });
  }
}
