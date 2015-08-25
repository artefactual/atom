<?php $sf_response->addJavaScript('date') ?>
<?php $sf_response->addJavaScript('/vendor/yui/datasource/datasource-min') ?>
<?php $sf_response->addJavaScript('/vendor/yui/container/container-min') ?>
<?php $sf_response->addJavaScript('dialog') ?>
<?php $sf_response->addJavaScript('multiDelete') ?>

<?php use_helper('Javascript') ?>

<div class="section">

  <table id="relatedFunctions" class="table table-bordered">
    <caption>
      <?php echo __('Related functions') ?>
    </caption><thead>
      <tr>
        <th style="width: 25%">
          <?php echo __('Name') ?>
        </th><th style="width: 15%">
          <?php echo __('Category') ?>
        </th><th style="width: 30%">
          <?php echo __('Description') ?>
        </th><th style="width: 20%">
          <?php echo __('Dates') ?>
        </th><th style="text-align: center; width: 10%">
        </th>
      </tr>
    </thead><tbody>
      <?php foreach ($isdf->relatedFunction as $item): ?>
        <tr class="<?php echo 0 == @++$row % 2 ? 'even' : 'odd' ?> related_obj_<?php echo $item->id ?>" id="<?php echo url_for(array($item, 'module' => 'relation')) ?>">
          <td>
            <?php if ($resource->id == $item->objectId): ?>
              <?php echo render_title($item->subject) ?>
            <?php else: ?>
              <?php echo render_title($item->object) ?>
            <?php endif; ?>
          </td><td>
            <?php echo $item->type ?>
          </td><td>
            <?php echo $item->description ?>
          </td><td>
            <?php echo Qubit::renderDateStartEnd($item->date, $item->startDate, $item->endDate) ?>
          </td><td style="text-align: center">
            <input class="multiDelete" name="deleteRelations[]" type="checkbox" value="<?php echo url_for(array($item, 'module' => 'relation')) ?>"/>
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
    {{$form->resource->renderName()}}
  </td><td>
    {{$form->type->renderName()}}
  </td><td>
    {{$form->description->renderName()}}
  </td><td>
    {{$form->date->renderName()}}
  </td><td style="text-align: right">
    $editHtml <button class="delete-small" name="delete" type="button"/>
  </td>
</tr>

value
);

$url = url_for($resource);

echo javascript_tag(<<<content

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

Drupal.behaviors.relatedFunction = {
  attach: function (context)
    {
      // Define dialog
      var dialog = new QubitDialog('functionRelation', {
        'displayTable': 'relatedFunctions',
        'newRowTemplate': $rowTemplate,
        'handleFieldRender': handleFieldRender,
        'relationTableMap': function (response)
          {
            response.resource = response.object;
            if ('$url' === response.resource)
            {
              response.resource = response.subject;
            }

            return response;
          } });

      // Add edit button to rows
      jQuery('#relatedFunctions tr[id]', context)
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
  <div class="date section" id="functionRelation">

    <h3><?php echo __('Related function') ?></h3>

    <div>

      <div class="form-item">
        <?php echo $form->resource
          ->label(__('Authorized form of name'))
          ->renderLabel() ?>
        <?php echo $form->resource->render(array('class' => 'form-autocomplete')) ?>
        <input class="list" type="hidden" value="<?php echo url_for(array('module' => 'function', 'action' => 'autocomplete')) ?>"/>
        <?php echo $form->resource
          ->help(__('"Record the authorised form of name and any unique identifier of the related function." (ISDF 5.3.1)'))
          ->renderHelp() ?>
      </div>

      <?php echo $form->type
        ->help(__('"Record a general category into which the relationship being described falls." (ISDF 5.3.2) Select a category from the drop-down menu: hierarchical, temporal or associative.'))
        ->label(__('Category'))
        ->renderRow() ?>

      <?php echo $form->description
        ->help(__('"Record a precise description of the nature of the relationship between the function being described and the related function." (ISDF 5.3.3) Note that the text entered in this field will also appear in the related function.'))
        ->renderRow() ?>

      <?php echo $form->date->renderRow() ?>

      <?php echo $form->startDate->renderRow() ?>

      <?php echo $form->endDate->renderRow() ?>

    </div>

  </div>

</div>
