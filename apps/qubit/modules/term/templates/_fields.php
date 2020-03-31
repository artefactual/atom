<?php echo render_show(__('Taxonomy'), link_to(render_title($resource->taxonomy), array($resource->taxonomy, 'module' => 'taxonomy'))) ?>

<div class="field">
  <h3><?php echo __('Code') ?></h3>
  <div>
    <?php echo $resource->code ?>
    <?php if (!empty($resource->code) && QubitTaxonomy::PLACE_ID == $resource->taxonomy->id): ?>
      <?php echo image_tag('https://maps.googleapis.com/maps/api/staticmap?zoom=13&size=300x300&sensor=false&center='.$resource->code,
        array('class' => 'static-map', 'alt' => __('Map of %1%',
        array('%1%' => truncate_text(strip_markdown($resource), 100))))) ?>
    <?php endif; ?>
  </div>
</div>

<div class="field">
  <h3><?php echo __('Scope note(s)') ?></h3>
  <div>
    <ul>
      <?php foreach ($resource->getNotesByType(array('noteTypeId' => QubitTerm::SCOPE_NOTE_ID)) as $item): ?>
        <?php if ($item->sourceCulture != $sf_user->getCulture()): ?>
          <?php continue; ?>
        <?php endif; ?>
        <li><?php echo render_value_inline($item->getContent(array('cultureFallback' => true))) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
</div>

<div class="field">
  <h3><?php echo __('Source note(s)') ?></h3>
  <div>
    <ul>
      <?php foreach ($resource->getNotesByType(array('noteTypeId' => QubitTerm::SOURCE_NOTE_ID)) as $item): ?>
        <li><?php echo render_value_inline($item->getContent(array('cultureFallback' => true))) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
</div>

<div class="field">
  <h3><?php echo __('Display note(s)') ?></h3>
  <div>
    <ul>
      <?php foreach ($resource->getNotesByType(array('noteTypeId' => QubitTerm::DISPLAY_NOTE_ID)) as $item): ?>
        <li><?php echo render_value_inline($item->getContent(array('cultureFallback' => true))) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
</div>

<div class="field">
  <h3><?php echo __('Hierarchical terms') ?></h3>
  <div>

    <?php if (QubitTerm::ROOT_ID != $resource->parent->id): ?>
      <?php echo render_show(
                   render_title($resource), __('BT %1%', array(
                     '%1%' => link_to(render_title($resource->parent), array($resource->parent, 'module' => 'term'))))) ?>
    <?php endif; ?>

    <div class="field">
      <h3><?php echo render_title($resource) ?></h3>
      <div>
        <ul>
          <?php foreach ($resource->getChildren(array('sortBy' => 'name')) as $item): ?>
            <li><?php echo __('NT %1%', array('%1%' => link_to(render_title($item), array($item, 'module' => 'term')))) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>

  </div>
</div>

<div class="field">
  <h3><?php echo __('Equivalent terms') ?></h3>
  <div>

    <div class="field">
      <h3><?php echo render_title($resource) ?></h3>
      <div>
        <ul>
          <?php foreach ($resource->otherNames as $item): ?>
            <?php if ($item->sourceCulture != $sf_user->getCulture()): ?>
              <?php continue; ?>
            <?php endif; ?>
            <li><?php echo __('UF %1%', array('%1%' => render_title($item))) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>

  </div>
</div>

<?php if (0 < count($converseTerms = QubitRelation::getBySubjectOrObjectId($resource->id, array('typeId' => QubitTerm::CONVERSE_TERM_ID)))): ?>
  <?php echo render_show(__('Converse term'), link_to(render_title(
               $converseTerms[0]->getOpposedObject($resource)), array($converseTerms[0]->getOpposedObject($resource), 'module' => 'term'))) ?>
<?php endif; ?>

<div class="field">
  <h3><?php echo __('Associated terms') ?></h3>
  <div>

    <div class="field">
      <h3><?php echo render_title($resource) ?></h3>
      <div>
        <ul>
          <?php foreach (QubitRelation::getBySubjectOrObjectId($resource->id, array('typeId' => QubitTerm::TERM_RELATION_ASSOCIATIVE_ID)) as $item): ?>
            <li><?php echo __('RT %1%', array('%1%' => link_to(render_title(
                        $item->getOpposedObject($resource->id)), array($item->getOpposedObject($resource->id), 'module' => 'term')))) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>

  </div>
</div>
