<?php echo render_show(__('Taxonomy'), link_to(render_title($resource->taxonomy), [$resource->taxonomy, 'module' => 'taxonomy'])); ?>

<div class="field">
  <h3><?php echo __('Code'); ?></h3>
  <div>
    <?php echo $resource->code; ?>
    <?php if (!empty($resource->code) && QubitTaxonomy::PLACE_ID == $resource->taxonomy->id) { ?>
      <?php echo image_tag('https://maps.googleapis.com/maps/api/staticmap?zoom=13&size=300x300&sensor=false&center='.$resource->code,
        ['class' => 'static-map', 'alt' => __('Map of %1%',
        ['%1%' => truncate_text(strip_markdown($resource), 100)])]); ?>
    <?php } ?>
  </div>
</div>

<div class="field">
  <h3><?php echo __('Scope note(s)'); ?></h3>
  <div>
    <ul>
      <?php foreach ($resource->getNotesByType(['noteTypeId' => QubitTerm::SCOPE_NOTE_ID]) as $item) { ?>
        <?php if ($item->sourceCulture != $sf_user->getCulture()) { ?>
          <?php continue; ?>
        <?php } ?>
        <li><?php echo render_value_inline($item->getContent(['cultureFallback' => true])); ?></li>
      <?php } ?>
    </ul>
  </div>
</div>

<div class="field">
  <h3><?php echo __('Source note(s)'); ?></h3>
  <div>
    <ul>
      <?php foreach ($resource->getNotesByType(['noteTypeId' => QubitTerm::SOURCE_NOTE_ID]) as $item) { ?>
        <li><?php echo render_value_inline($item->getContent(['cultureFallback' => true])); ?></li>
      <?php } ?>
    </ul>
  </div>
</div>

<div class="field">
  <h3><?php echo __('Display note(s)'); ?></h3>
  <div>
    <ul>
      <?php foreach ($resource->getNotesByType(['noteTypeId' => QubitTerm::DISPLAY_NOTE_ID]) as $item) { ?>
        <li><?php echo render_value_inline($item->getContent(['cultureFallback' => true])); ?></li>
      <?php } ?>
    </ul>
  </div>
</div>

<div class="field">
  <h3><?php echo __('Hierarchical terms'); ?></h3>
  <div>

    <?php if (QubitTerm::ROOT_ID != $resource->parent->id) { ?>
      <?php echo render_show(
                   render_title($resource), __('BT %1%', [
                       '%1%' => link_to(render_title($resource->parent), [$resource->parent, 'module' => 'term']), ])); ?>
    <?php } ?>

    <div class="field">
      <h3><?php echo render_title($resource); ?></h3>
      <div>
        <ul>
          <?php foreach ($resource->getChildren(['sortBy' => 'name']) as $item) { ?>
            <li><?php echo __('NT %1%', ['%1%' => link_to(render_title($item), [$item, 'module' => 'term'])]); ?></li>
          <?php } ?>
        </ul>
      </div>
    </div>

  </div>
</div>

<div class="field">
  <h3><?php echo __('Equivalent terms'); ?></h3>
  <div>

    <div class="field">
      <h3><?php echo render_title($resource); ?></h3>
      <div>
        <ul>
          <?php foreach ($resource->otherNames as $item) { ?>
            <?php if ($item->sourceCulture != $sf_user->getCulture()) { ?>
              <?php continue; ?>
            <?php } ?>
            <li><?php echo __('UF %1%', ['%1%' => render_title($item)]); ?></li>
          <?php } ?>
        </ul>
      </div>
    </div>

  </div>
</div>

<?php if (0 < count($converseTerms = QubitRelation::getBySubjectOrObjectId($resource->id, ['typeId' => QubitTerm::CONVERSE_TERM_ID]))) { ?>
  <?php echo render_show(__('Converse term'), link_to(render_title(
               $converseTerms[0]->getOpposedObject($resource)), [$converseTerms[0]->getOpposedObject($resource), 'module' => 'term'])); ?>
<?php } ?>

<div class="field">
  <h3><?php echo __('Associated terms'); ?></h3>
  <div>

    <div class="field">
      <h3><?php echo render_title($resource); ?></h3>
      <div>
        <ul>
          <?php foreach (QubitRelation::getBySubjectOrObjectId($resource->id, ['typeId' => QubitTerm::TERM_RELATION_ASSOCIATIVE_ID]) as $item) { ?>
            <li><?php echo __('RT %1%', ['%1%' => link_to(render_title(
                          $item->getOpposedObject($resource->id)), [$item->getOpposedObject($resource->id), 'module' => 'term'])]); ?></li>
          <?php } ?>
        </ul>
      </div>
    </div>

  </div>
</div>
