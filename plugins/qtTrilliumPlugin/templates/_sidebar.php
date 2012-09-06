<div class="column sidebar" id="sidebar-first">
  <div class="section">

    <?php echo get_component_slot('sidebar') ?>

    <?php echo get_component('menu', 'browseMenu', array('sf_cache_key' => $sf_user->getCulture().$sf_user->getUserID())) ?>

  </div> <!-- /.section -->
</div> <!-- /.column.sidebar#sidebar-first -->
