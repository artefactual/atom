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

<div id="modalContainer"></div>

<script type="text/javascript">
  (function(){
    var container = jQuery('#modalContainer')

    function setFieldsForBasis()
    {
      console.log('updating fields for basis');
      var selected = container.find('select[name=basis]')[0];
      var type = false;
      if(selected) {
        type = jQuery(selected.selectedOptions[0]).text().toLowerCase();
      }

      jQuery('#modalContainer .basis-group').each(function(){
        $this = jQuery(this);
        $this.toggle($this.hasClass(type));
      });
    }

    // setup Edit button events
    // fetch modal html from server
    // inject into #modalContainer
    // display the modal
    // call any prep required
    jQuery('#rightsArea').on('click', '[data-modal=true]', function(event){
      event.preventDefault();
      
      // clear anything in the modalContainer
      container.empty()

      var $this = jQuery(this);
      var data = {};

      // pass data-id value (rights record id)
      if($this.attr('data-id')) {
        data.id = $this.attr('data-id');
      }
      jQuery.get('/right/modal', data, function(data) {
        container.append(data);
        container.find('.modal').modal('show');
        setFieldsForBasis();
      });
    });

    // add event listener for basis select changes
    container.on('change','select[name="basis"]', setFieldsForBasis);

    // // add event listener for submit buttons
    // jQuery('.modal').on('click', '.btn-primary', function(){
    //   var form = jQuery(this).parent().parent().find('form');
    //   jQuery.post(form.attr('action'), form.serialize());
    // });
  })();
</script>