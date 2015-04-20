<?php $sf_response->addJavaScript('/vendor/yui/datasource/datasource-min') ?>
<?php $sf_response->addJavaScript('/vendor/yui/container/container-min') ?>
<?php $sf_response->addJavaScript('/vendor/yui/tabview/tabview-min') ?>
<?php $sf_response->addJavaScript('dialog') ?>
<?php $sf_response->addJavaScript('multiDelete') ?>

<?php use_helper('Javascript') ?>

<div class="section">

  <table id="relatedDonorDisplay" class="table table-bordered">
    <caption>
      <?php echo __('Related donors') ?>
    </caption><thead>
      <tr>
        <th colspan="2">
          <?php echo __('Name') ?>
        </th>
      </tr>
    </thead><tbody>
      <?php foreach ($relatedDonorRecord as $item): ?>
        <tr class="<?php echo 0 == @@++$row % 2 ? 'even' : 'odd' ?> related_obj_<?php echo $item->id ?>" id="<?php echo url_for(array($item, 'module' => 'accession', 'action' => 'relatedDonor')) ?>">
          <td>
            <?php echo render_title($item->object) ?>
          </td><td style="text-align: right;">
            <input class="multiDelete" name="deleteRelations[]" type="checkbox" value="<?php echo url_for(array($item, 'module' => 'relation')) ?>"/>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

<?php

// Template for new display table rows
$editHtml = image_tag('pencil', array('alt' => 'edit', 'style' => 'align: top'));

$rowTemplate = json_encode(<<<value
<tr id="{{$form->getWidgetSchema()->generateName('id')}}">
  <td>
    {{$form->resource->renderName()}}
  </td><td style="text-align: right">
    $editHtml <button class="delete-small" name="delete" type="button"/>
  </td>
</tr>

value
);

echo javascript_tag(<<<content
Drupal.behaviors.relatedAuthorityRecord = {
  attach: function (context)
    {
      // Define dialog
      var dialog = new QubitDialog('relatedDonor', {
        'displayTable': 'relatedDonorDisplay',
        'newRowTemplate': $rowTemplate,
        'relationTableMap': function (response)
          {
            response.resource = response.object;

            return response;
          } });

      // Add edit button to rows
      jQuery('#relatedDonorDisplay tr[id]', context)
        .click(function ()
          {
            dialog.open(this.id);
          })
        .find('td:last')
        .prepend('$editHtml');

      // Load primary contact data when a new item is selected
      jQuery('#relatedDonor .yui-ac-input').on('itemSelected', function (e)
        {
          dialog.loadData(e.itemValue + '/donor/primaryContact', function ()
            {
              dialog.yuiDialog.show();
            });
        });

    } }

content
) ?>

  <!-- NOTE dialog.js wraps this *entire* table in a YUI dialog -->
  <div class="section" id="relatedDonor">

    <h3><?php echo __('Related donor record') ?></h3>

    <div>

      <?php echo $form->renderHiddenFields() ?>

      <div class="form-item">
        <?php echo $form->resource
          ->label(__('Name'))
          ->renderLabel() ?>
        <?php echo $form->resource->render(array('class' => 'form-autocomplete')) ?>
        <?php echo $form->resource
          ->help(__('This is the legal entity field and provides the contact information for the person(s) or the institution that donated or transferred the materials. It has the option of multiple instances and provides the option of creating more than one contact record using the same form.'))
          ->renderHelp() ?>
        <input class="add" type="hidden" value="<?php echo url_for(array('module' => 'donor', 'action' => 'add')) ?> #authorizedFormOfName"/>
        <input class="list" type="hidden" value="<?php echo url_for(array('module' => 'donor', 'action' => 'autocomplete')) ?>"/>
      </div>

      <fieldset>

        <legend><?php echo __('Primary contact information') ?></legend>

        <div id="contactInformationRelationTabView" class="yui-navset">

          <ul class="yui-nav">

            <li class="selected"><a href="#contactInformationRelation_Tab1"><em><?php echo __('Main') ?></em></a></li>
            <li><a href="#contactInformationRelation_Tab2"><em><?php echo __('Physical location') ?></em></a></li>
            <li><a href="#contactInformationRelation_Tab3"><em><?php echo __('Other details') ?></em></a></li>

          </ul>

          <div class="yui-content">

            <div id="contactInformationRelation_Tab1">

              <?php echo $form->contactPerson->renderRow() ?>

              <?php echo $form->telephone->renderRow() ?>

              <?php echo $form->fax->renderRow() ?>

              <?php echo $form->email->renderRow() ?>

              <?php echo $form->website
                ->label(__('URL'))
                ->renderRow() ?>

            </div>

            <div id="contactInformationRelation_Tab2">

              <?php echo $form->streetAddress->renderRow() ?>

              <?php echo $form->region
                ->label(__('Region/province'))
                ->renderRow() ?>

              <?php echo $form->countryCode
                ->label(__('Country'))
                ->renderRow() ?>

              <?php echo $form->postalCode->renderRow() ?>

              <?php echo $form->city->renderRow() ?>

              <?php echo $form->latitude->renderRow() ?>

              <?php echo $form->longitude->renderRow() ?>

            </div>

            <div id="contactInformationRelation_Tab3">

              <?php echo $form->contactType->renderRow() ?>

              <?php echo $form->note->renderRow() ?>

            </div>

          </div>

        </div>

      </fieldset>

    </div>

  </div>

</div>
