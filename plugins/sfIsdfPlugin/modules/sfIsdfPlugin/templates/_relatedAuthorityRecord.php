<?php $sf_response->addJavaScript('date', 'last'); ?>
<?php $sf_response->addJavaScript('/vendor/yui/datasource/datasource-min', 'last'); ?>
<?php $sf_response->addJavaScript('/vendor/yui/container/container-min', 'last'); ?>
<?php $sf_response->addJavaScript('dialog', 'last'); ?>
<?php $sf_response->addJavaScript('multiDelete', 'last'); ?>

<?php use_helper('Javascript'); ?>

<div class="section">

  <table id="relatedEntityDisplay" class="table table-bordered">
    <caption>
      <?php echo __('Related authority records'); ?>
    </caption><thead>
      <tr>
        <th style="width: 25%">
          <?php echo __('Identifier/name'); ?>
        </th><th style="width: 30%">
          <?php echo __('Nature of relationship'); ?>
        </th><th style="width: 20%">
          <?php echo __('Dates'); ?>
        </th><th style="text-align: center; width: 10%">
        </th>
      </tr>
    </thead><tbody>
      <?php foreach ($isdf->relatedAuthorityRecord as $item) { ?>
        <tr class="<?php echo 0 == @++$row % 2 ? 'even' : 'odd'; ?> related_obj_<?php echo $item->id; ?>" id="<?php echo url_for([$item, 'module' => 'relation']); ?>">
          <td>
            <?php echo render_title($item->object); ?>
          </td><td>
            <?php echo render_value_inline($item->description); ?>
          </td><td>
            <?php echo render_value_inline(Qubit::renderDateStartEnd($item->date, $item->startDate, $item->endDate)); ?>
          </td><td style="text-align: right">
            <input class="multiDelete" name="deleteRelations[]" type="checkbox" value="<?php echo url_for([$item, 'module' => 'relation']); ?>"/>
          </td>
        </tr>
      <?php } ?>
    </tbody>
  </table>

<?php

// Template for new display table rows
$editHtml = image_tag('pencil', ['alt' => __('Edit'), 'style' => 'align: top']);

$rowTemplate = json_encode(<<<value
<tr id="{{$form->getWidgetSchema()->generateName('id')}}">
  <td>
    {{$form->resource->renderName()}}
  </td><td>
    {{$form->description->renderName()}}
  </td><td>
    {{$form->date->renderName()}}
  </td><td style="text-align: right">
    {$editHtml} <button class="delete-small" name="delete" type="button"/>
  </td>
</tr>

value
);

echo javascript_tag(<<<content
Drupal.behaviors.relatedAuthorityRecord = {
  attach: function (context)
    {
      // Add validator to ensure a related record is selected
      var validator = function(data) {
          var relation = data["relatedAuthorityRecord[resource]"];

          if (!relation.length) {
            // Display error message
            jQuery('#relatedEntityError').css('display', 'block');
            
            return false;
          } else {
            // Hide error message until required again
            jQuery('#relatedEntityError').css('display', 'none');
          }
      }

      // Hide error on cancel
      var afterCancelLogic = function () {
          jQuery('#relatedEntityError').css('display', 'none');
      }

      // Define dialog
      var dialog = new QubitDialog('relatedEntity', {
        'displayTable': 'relatedEntityDisplay',
        'handleFieldRender': handleFieldRender,
        'newRowTemplate': {$rowTemplate},
        'validator': validator,
        'afterCancelLogic': afterCancelLogic,
        'relationTableMap': function (response)
          {
            response.resource = response.object;

            return response;
          } });

      // Add edit button to rows
      jQuery('#relatedEntityDisplay tr[id]', context)
        .click(function ()
          {
            dialog.open(this.id);
          })
        .find('td:last')
        .prepend('{$editHtml}');
    } }

content
); ?>

  <!-- NOTE dialog.js wraps this *entire* table in a YUI dialog -->
  <div class="date section" id="relatedEntity">

    <h3><?php echo __('Related authority record'); ?></h3>

    <div>

      <div class="messages error" id="relatedEntityError" style="display: none">
        <ul>
          <li><?php echo __('Please complete all required fields.'); ?></li>
        </ul>
      </div>

      <div class="form-item">
        <?php echo $form->resource
            ->label(__('Authorized form of name'))
            ->renderLabel(); ?>
        <?php echo $form->resource->render(['class' => 'form-autocomplete']); ?>
        <input class="list" type="hidden" value="<?php echo url_for(['module' => 'actor', 'action' => 'autocomplete', 'showOnlyActors' => 'true']); ?>"/>
        <?php echo $form->resource
            ->help(__('Select the name from the drop-down menu; enter the identifier or the first few letters to narrow the choices. (ISDF 6.1)'))
            ->renderHelp(); ?>
      </div>

      <?php echo $form->description
          ->label(__('Nature of relationship'))
          ->renderRow(); ?>

      <?php echo $form->date->renderRow(); ?>

      <?php echo $form->startDate->renderRow(); ?>

      <?php echo $form->endDate->renderRow(); ?>

    </div>

  </div>

</div>
