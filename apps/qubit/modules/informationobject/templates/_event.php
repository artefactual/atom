<?php

$sf_response->addJavaScript('date', 'last');
$sf_response->addJavaScript('/vendor/yui/datasource/datasource-min', 'last');
$sf_response->addJavaScript('/vendor/yui/container/container-min', 'last');
$sf_response->addJavaScript('dialog', 'last');

use_helper('Javascript');

// Template for new display table rows
$editHtml = image_tag('pencil', ['alt' => __('Edit'), 'style' => 'align: top']);

$rowTemplate = json_encode(<<<value
<tr id="{{$event->getWidgetSchema()->generateName('id')}}">
  <td>
    {{$event->actor->renderName()}}
  </td><td>
    {{$event->type->renderName()}}
  </td><td>
    {{$event->place->renderName()}}
  </td><td>
    {{$event->date->renderName()}}
  </td><td style="text-align: right">
    {$editHtml} <button class="delete-small" name="delete" type="button"/>
  </td>
</tr>

value
);

// Omit edit button if object is being duplicated
$editButtonJs = null;
if (!isset($sf_request->source)) {
    $editButtonJs = <<<editButtonJs
// Add edit button to rows
jQuery('#relatedEvents tr[id]', context)
  .click(function ()
    {
      dialog.open(this.id);
    })
  .find('td:last')
  .prepend('{$editHtml}');

editButtonJs;
}

echo javascript_tag(<<<content
Drupal.behaviors.event = {
  attach: function (context)
    {
      // Add special rendering rules
      var handleFieldRender = function (fname)
        {
          if (-1 !== fname.indexOf('date')
              && 1 > this.getField('date').value.length
              && (0 < this.getField('startDate').value.length
                || 0 < this.getField('endDate').value.length))
          {
            return this.getField('startDate').value + ' - ' + this.getField('endDate').value;
          }

          return this.renderField(fname);
        }

      // Define dialog
      var dialog = new QubitDialog('updateEvent', {
        'displayTable': 'relatedEvents',
        'handleFieldRender': handleFieldRender,
        'newRowTemplate': {$rowTemplate} });

      {$editButtonJs}
    } }

content
); ?>

<!-- NOTE dialog.js wraps this *entire* table in a YUI dialog -->
<div class="date section" id="updateEvent">

  <h3><?php echo __('Event'); ?></h3>

  <div class="form-item">

    <?php echo $event->actor
        ->label(__('Actor name'))
        ->renderLabel(); ?>
    <?php echo $event->actor->render(['class' => 'form-autocomplete']); ?>

    <?php if (QubitAcl::check(QubitActor::getRoot(), 'create')) { ?>
      <input class="add" type="hidden" data-link-existing="true" value="<?php echo url_for(['module' => 'actor', 'action' => 'add']); ?> #authorizedFormOfName"/>
    <?php } ?>

    <input class="list" type="hidden" value="<?php echo url_for(['module' => 'actor', 'action' => 'autocomplete']); ?>"/>
    <?php echo $event->actor->renderHelp(); ?>

  </div>

  <?php echo $event->type
      ->label(__('Event type'))
      ->renderRow(); ?>

  <div class="form-item form-item-place">

    <?php echo $event->place->renderLabel(); ?>
    <?php echo $event->place->render(['class' => 'form-autocomplete']); ?>

    <?php if (QubitAcl::check(QubitTaxonomy::getById(QubitTaxonomy::PLACE_ID), 'createTerm')) { ?>
      <input class="add" type="hidden" data-link-existing="true" value="<?php echo url_for(['module' => 'term', 'action' => 'add', 'taxonomy' => url_for([QubitTaxonomy::getById(QubitTaxonomy::PLACE_ID), 'module' => 'taxonomy'])]); ?> #name"/>
    <?php } ?>

    <input class="list" type="hidden" value="<?php echo url_for(['module' => 'term', 'action' => 'autocomplete', 'taxonomy' => url_for([QubitTaxonomy::getById(QubitTaxonomy::PLACE_ID), 'module' => 'taxonomy'])]); ?>"/>
    <?php echo $event->place->renderHelp(); ?>

  </div>

  <?php echo $event->date->renderRow(); ?>

  <?php echo $event->startDate->renderRow(); ?>

  <?php echo $event->endDate->renderRow(); ?>

  <?php echo $event->description
      ->label(__('Event note'))
      ->renderRow(); ?>

</div>
