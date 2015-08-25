<?php $sf_response->addJavaScript('date') ?>
<?php $sf_response->addJavaScript('/vendor/yui/datasource/datasource-min') ?>
<?php $sf_response->addJavaScript('/vendor/yui/container/container-min') ?>
<?php $sf_response->addJavaScript('dialog') ?>
<?php $sf_response->addJavaScript('multiDelete') ?>

<?php use_helper('Javascript') ?>

<div class="section">

  <table id="relatedEvents" class="table table-bordered">
    <caption>
      <?php echo __('Related resources') ?>
    </caption><thead>
      <tr>
        <th style="width: 35%">
          <?php echo __('Title') ?>
        </th><th style="width: 20%">
          <?php echo __('Relationship') ?>
        </th><th style="width: 25%">
          <?php echo __('Dates') ?>
        </th><th style="text-align: center; width: 10%">
        </th>
      </tr>
    </thead><tbody>
      <?php foreach ($resource->getEvents() as $item): ?>
        <tr class="<?php echo 0 == @++$row % 2 ? 'even' : 'odd' ?> related_obj_<?php echo $item->id ?>" id="<?php echo url_for(array($item, 'module' => 'event')) ?>">
          <td>
            <?php echo render_title($item->object) ?>
          </td><td>
            <?php echo $item->type ?>
          </td><td>
            <?php echo Qubit::renderDateStartEnd($item->date, $item->startDate, $item->endDate) ?>
          </td><td style="text-align: right">
            <input class="multiDelete" name="deleteEvents[]" type="checkbox" value="<?php echo url_for(array($item, 'module' => 'event')) ?>"/>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

<?php

// Template for new display table rows
$editHtml = image_tag('pencil', array('alt' => __('Edit'), 'style' => 'align: top'));

$rowTemplate = json_encode(<<<value
<tr id="{{$form->getWidgetSchema()->generateName('id')}}">
  <td>
    {{$form->informationObject->renderName()}}
  </td><td>
    {{$form->type->renderName()}}
  </td><td>
    {{$form->date->renderName()}}
  </td><td style="text-align: right">
    $editHtml <button class="delete-small" name="delete" type="button"/>
  </td>
</tr>

value
);

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

      // Add validator to make sure that an information object is selected
      var validator = function (data)
        {
          var informationObject = data['editEvent[informationObject]'];
          if (!informationObject.length)
          {
            return false;
          }
        }

      // Define dialog
      var dialog = new QubitDialog('resourceRelation', {
        'displayTable': 'relatedEvents',
        'handleFieldRender': handleFieldRender,
        'newRowTemplate': $rowTemplate,
        'validator': validator });

      // Add edit button to rows
      jQuery('#relatedEvents tr[id]', context)
        .click(function ()
          {
            dialog.open(this.id);
          })
        .find('td:last')
        .prepend('$editHtml');
    } }

content
) ?>

  <!-- NOTE dialog.js wraps this *entire* table in a YUI dialog -->
  <div class="date section" id="resourceRelation">

    <div class="form-item">
      <?php echo $form->informationObject
        ->label(__('Title of related resource'))
        ->renderLabel() ?>
      <?php echo $form->informationObject->render(array('class' => 'form-autocomplete')) ?>
      <input class="list" type="hidden" value="<?php echo url_for(array('module' => 'informationobject', 'action' => 'autocomplete')) ?>"/>
      <?php echo $form->informationObject
        ->help(__('"Provide the unique identifiers/reference codes and/or titles for the related resources." (ISAAR 6.1) Select the title from the drop-down menu; enter the identifier or the first few letters to narrow the choices.'))
        ->renderHelp() ?>
    </div>

    <?php echo $form->type
      ->help(__('"Describe the nature of the relationships between the corporate body, person or family and the related resource." (ISAAR 6.3) Select the type of relationship from the drop-down menu; these values are drawn from the Event Types taxonomy.'))
      ->label(__('Nature of relationship'))
      ->renderRow() ?>

    <?php echo $form->resourceType
      ->help(__('"Identify the type of related resources, e.g. Archival materials (fonds, record series, etc), archival description, finding aid, monograph, journal article, web site, photograph, museum collection, documentary film, oral history recording." (ISAAR 6.2) In the current version of the software, Archival material is provided as the only default value.'))
      ->label(__('Type of related resource'))
      ->renderRow(array('disabled' => 'true', 'class' => 'disabled')) ?>

    <?php echo $form->date
      ->help(__('"Provide any relevant dates for the related resources and/or the relationship between the corporate body, person or family and the related resource." (ISAAR 6.4) Enter the date as you would like it to appear in the show page for the authority record, using qualifiers and/or typographical symbols to express uncertainty if desired.'))
      ->renderRow() ?>

    <?php echo $form->startDate->renderRow() ?>

    <?php echo $form->endDate->renderRow() ?>

  </div>

</div>
