
<?php if ($resource instanceof QubitInformationObject): ?>

  <?php foreach ($ancestors as $item): ?>

    <?php foreach ($item->getRights() as $right): ?>

      <?php echo get_partial('right/right',
        array(
          'resource' => $right->object,
          'inherit' => $item != $resource ? $item : null)) ?>

    <?php endforeach; ?>

  <?php endforeach; ?>

<?php elseif ($resource instanceof QubitAccession): ?>

  <?php foreach ($ancestor->getRights() as $item): ?>

    <?php echo get_partial('right/right',
      array(
        'resource' => $item->object,
        'inherit' => 0 == count($resource->getRights()) ? $resource : null)) ?>

  <?php endforeach; ?>

<?php endif; ?>

<a data-modal="true"><h2>Create New Right</h2></a>

<div id="modalContainer"></div>

<style type="text/css">
  .act-description:hover {
    cursor: pointer;
    background-color:#eee;
  }
</style>

<script type="text/javascript">
  (function(){
    var container = jQuery('#modalContainer')

    function setFieldsForBasis()
    {
      var selected = container.find('select[name=basis]')[0];
      var type = false;
      if(selected) {
        type = jQuery(selected.selectedOptions[0]).text().toLowerCase();
      }

      container.find('.basis-group').each(function(){
        $this = jQuery(this);
        $this.toggle($this.hasClass(type));
      });
    }

    function postModalSuccess(data) {
      // look for pre-existing instance of rights html
      var target = jQuery('#rightsArea').find('#'+jQuery(data).attr('id'));
      if(target.length)
      {
        // replace it with the new one
        target.replaceWith(data);
      } else {
        // append new rights html as last right in #rightsArea
        var append = jQuery('#rightsArea > div.field:last');
        if(append.length)
        {
          append.after(data);
        } else { 
          // if no existing rights to append to, use first anchor
          // as a reference point
          jQuery('#rightsArea > a:first').after(data);
        }
      }

      // jQuery('#rightsArea').find('#'+jQuery(data).attr('id')).replaceWith(data);
      container.find('.modal').modal('hide');
    }

    // setup Edit button events
    // fetch modal html from server
    // inject into #modalContainer
    // display the modal
    // call any prep required
    jQuery('#rightsArea').on('click', '[data-modal=true]', function(event){
      
      // clear anything in the modalContainer
      container.empty()

      var $this = jQuery(this);
      var data = {};

      // pass data-id value (rights record id)
      if($this.attr('data-id')) {
        data.id = $this.attr('data-id');
      }
      // extract slug from end of url.
      data.object_slug = document.location.href.match(/[\w\d-]*$/)[0];
      jQuery.get('/right/modal', data, function(data) {
        container.append(data);
        container.find('.modal').modal('show');
        setFieldsForBasis();
      });

      return false;
    });

    // add event listener for basis select changes
    container.on('change','select[name="basis"]', setFieldsForBasis);

    // add event listener for submit buttons
    container.on('click', '.btn-primary', function(){
      var form = container.find('form');
      var data = form.serialize();
      // extract slug from end of url.
      data.object_slug = document.location.href.match(/[\w\d-]*$/)[0];
      jQuery.post(form.attr('action'), data, postModalSuccess);
    });
  })();
</script>