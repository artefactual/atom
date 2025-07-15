<section id="action-icons">

  <h4 class="h5 mb-2"><?php echo __('Clipboard'); ?></h4>
  <ul class="list-unstyled">
    <li>
      <?php echo get_component('clipboard', 'button', ['slug' => $resource->slug, 'wide' => true, 'type' => 'informationObject']); ?>
    </li>
  </ul>

</section>
