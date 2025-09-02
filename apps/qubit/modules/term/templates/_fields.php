<?php echo render_show(__('Taxonomy'), link_to(render_title($resource->taxonomy), [$resource->taxonomy, 'module' => 'taxonomy'])); ?>

<div class="field <?php echo render_b5_show_field_css_classes(); ?>">
  <?php echo render_b5_show_label(__('Code')); ?>
  <div class="<?php echo render_b5_show_value_css_classes(); ?>">
    <?php echo $resource->code; ?>
    <?php if (!empty($resource->code) && QubitTaxonomy::PLACE_ID == $resource->taxonomy->id && $mapApiKey = sfConfig::get('app_google_maps_api_key')) { ?>
      <?php echo image_tag('https://maps.googleapis.com/maps/api/staticmap?zoom=13&size=300x300&sensor=false&key='.$mapApiKey.'&center='.$resource->code,
        ['class' => 'img-thumbnail d-block mt-2', 'alt' => __('Map of %1%',
        ['%1%' => truncate_text(strip_markdown($resource), 100)])]); ?>
    <?php } ?>
  </div>
</div>

<?php
    $scopeNotes = [];
    foreach ($resource->getNotesByType(['noteTypeId' => QubitTerm::SCOPE_NOTE_ID]) as $item) {
        $scopeNotes[] = $item->getContent(['cultureFallback' => true]);
    }
    echo render_show(__('Scope note(s)'), $scopeNotes);
?>

<?php
    $sourceNotes = [];
    foreach ($resource->getNotesByType(['noteTypeId' => QubitTerm::SOURCE_NOTE_ID]) as $item) {
        $sourceNotes[] = $item->getContent(['cultureFallback' => true]);
    }
    echo render_show(__('Source note(s)'), $sourceNotes);
?>

<?php
    $displayNotes = [];
    foreach ($resource->getNotesByType(['noteTypeId' => QubitTerm::DISPLAY_NOTE_ID]) as $item) {
        $displayNotes[] = $item->getContent(['cultureFallback' => true]);
    }
    echo render_show(__('Display note(s)'), $displayNotes);
?>

<div class="field <?php echo render_b5_show_field_css_classes(); ?>">
  <?php echo render_b5_show_label(__('Hierarchical terms')); ?>
  <div class="<?php echo render_b5_show_value_css_classes(); ?>">
    <?php if (QubitTerm::ROOT_ID != $resource->parent->id) { ?>
      <?php echo render_show(
          render_title($resource),
          __('BT %1%', ['%1%' => link_to(render_title($resource->parent), [$resource->parent, 'module' => 'term'])]),
          ['isSubField' => true]
      ); ?>
    <?php } ?>

    <?php
        $hierarchicalTerms = [];
        foreach ($resource->getChildren(['sortBy' => 'name']) as $item) {
            $hierarchicalTerms[] = __('NT %1%', ['%1%' => link_to(render_title($item), [$item, 'module' => 'term'])]);
        }
        echo render_show(
            render_title($resource),
            $hierarchicalTerms,
            ['isSubField' => true]
        );
    ?>
  </div>
</div>

<div class="field <?php echo render_b5_show_field_css_classes(); ?>">
  <?php echo render_b5_show_label(__('Equivalent terms')); ?>
  <div class="<?php echo render_b5_show_value_css_classes(); ?>">
    <?php
        $equivalentTerms = [];
        foreach ($resource->otherNames as $item) {
            $equivalentTerms[] = __('UF %1%', ['%1%' => render_title($item)]);
        }
        echo render_show(render_title($resource), $equivalentTerms, ['isSubField' => true]);
    ?>
  </div>
</div>

<?php if (0 < count($converseTerms = QubitRelation::getBySubjectOrObjectId($resource->id, ['typeId' => QubitTerm::CONVERSE_TERM_ID]))) { ?>
  <?php echo render_show(__('Converse term'), link_to(render_title(
               $converseTerms[0]->getOpposedObject($resource)), [$converseTerms[0]->getOpposedObject($resource), 'module' => 'term'])); ?>
<?php } ?>

<div class="field <?php echo render_b5_show_field_css_classes(); ?>">
  <?php echo render_b5_show_label(__('Associated terms')); ?>
  <div class="<?php echo render_b5_show_value_css_classes(); ?>">
    <?php
        $associatedTerms = [];
        foreach (QubitRelation::getBySubjectOrObjectId($resource->id, ['typeId' => QubitTerm::TERM_RELATION_ASSOCIATIVE_ID]) as $item) {
            $associatedTerms[] = __('RT %1%', ['%1%' => link_to(render_title($item->getOpposedObject($resource->id)), [$item->getOpposedObject($resource->id), 'module' => 'term'])]);
        }
        echo render_show(render_title($resource), $associatedTerms, ['isSubField' => true]);
    ?>
  </div>
</div>
