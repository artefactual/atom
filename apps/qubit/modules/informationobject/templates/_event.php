<?php

$sf_response->addJavaScript('date');
$sf_response->addJavaScript('/vendor/yui/datasource/datasource-min');
$sf_response->addJavaScript('/vendor/yui/container/container-min');
$sf_response->addJavaScript('dialog');

use_helper('Javascript');

// Template for new display table rows
$editHtml = image_tag('pencil', array('alt' => __('Edit'), 'style' => 'align: top'));

$rowTemplate = json_encode(<<<value
<tr id="{{$form->getWidgetSchema()->generateName('id')}}">
  <td>
    {{$form->actor->renderName()}}
  </td><td>
    {{$form->type->renderName()}}
  </td><td>
    {{$form->place->renderName()}}
  </td><td>
    {{$form->date->renderName()}}
  </td><td style="text-align: right">
    $editHtml <button class="delete-small" name="delete" type="button"/>
  </td>
</tr>

value
);

// Omit edit button if object is being duplicated
$editButtonJs = null;
if (!isset($sf_request->source))
{
  $editButtonJs = <<<editButtonJs
// Add edit button to rows
jQuery('#relatedEvents tr[id]', context)
  .click(function ()
    {
      dialog.open(this.id);
    })
  .find('td:last')
  .prepend('$editHtml');

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
        'newRowTemplate': $rowTemplate });

      $editButtonJs
    } }

content
) ?>

<!-- NOTE dialog.js wraps this *entire* table in a YUI dialog -->
<div class="date section" id="updateEvent">

  <h3><?php echo __('Event') ?></h3>

  <div class="form-item">

    <?php echo $form->actor
      ->label(__('Actor name'))
      ->renderLabel() ?>
    <?php echo $form->actor->render(array('class' => 'form-autocomplete')) ?>

    <?php if (QubitAcl::check(QubitActor::getRoot(), 'create')): ?>
      <input class="add" type="hidden" data-link-existing="true" value="<?php echo url_for(array('module' => 'actor', 'action' => 'add')) ?> #authorizedFormOfName"/>
    <?php endif; ?>

    <input class="list" type="hidden" value="<?php echo url_for(array('module' => 'actor', 'action' => 'autocomplete')) ?>"/>
    <?php echo $form->actor->renderHelp() ?>

  </div>

  <?php echo $form->type
    ->label(__('Event type'))
    ->renderRow() ?>

  <div class="form-item form-item-place">

    <?php echo $form->place->renderLabel() ?>
    <?php echo $form->place->render(array('class' => 'form-autocomplete')) ?>

    <?php if (QubitAcl::check(QubitTaxonomy::getById(QubitTaxonomy::PLACE_ID), 'createTerm')): ?>
      <input class="add" type="hidden" data-link-existing="true" value="<?php echo url_for(array('module' => 'term', 'action' => 'add', 'taxonomy' => url_for(array(QubitTaxonomy::getById(QubitTaxonomy::PLACE_ID), 'module' => 'taxonomy')))) ?> #name"/>
    <?php endif; ?>

    <input class="list" type="hidden" value="<?php echo url_for(array('module' => 'term', 'action' => 'autocomplete', 'taxonomy' => url_for(array(QubitTaxonomy::getById(QubitTaxonomy::PLACE_ID), 'module' => 'taxonomy')))) ?>"/>
    <?php echo $form->place->renderHelp() ?>

  </div>

  <?php echo $form->date->renderRow() ?>

  <?php echo $form->startDate->renderRow() ?>

  <?php echo $form->endDate->renderRow() ?>

  <?php echo $form->description
    ->label(__('Event note'))
    ->renderRow() ?>

</div>
