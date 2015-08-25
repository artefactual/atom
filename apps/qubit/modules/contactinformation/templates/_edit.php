<?php $sf_response->addJavaScript('/vendor/yui/connection/connection-min') ?>
<?php $sf_response->addJavaScript('/vendor/yui/datasource/datasource-min') ?>
<?php $sf_response->addJavaScript('/vendor/yui/container/container-min') ?>
<?php $sf_response->addJavaScript('/vendor/yui/tabview/tabview-min') ?>
<?php $sf_response->addJavaScript('dialog') ?>
<?php $sf_response->addJavaScript('multiDelete') ?>

<?php use_helper('Javascript') ?>

<div class="section">

  <table class="table table-bordered" id="contactInformations">
    <caption>
      <?php echo __('Related contact information') ?>
    </caption><thead>
      <tr>
        <th style="width: 60%">
          <?php echo __('Contact person') ?>
        </th><th style="width: 15%">
          <?php echo __('Primary') ?>
        </th><th style="text-align: center; width: 10%">
        </th>
      </tr>
    </thead><tbody>
      <?php foreach ($resource->contactInformations as $item): ?>
        <tr class="<?php echo 0 == @++$row % 2 ? 'even' : 'odd' ?> related_obj_<?php echo $item->id ?>" id="<?php echo url_for(array($item, 'module' => 'contactinformation')) ?>">
          <td>
            <?php echo $item->contactPerson ?>
          </td><td>
            <input type="checkbox"<?php echo $item->primaryContact ? " checked=\"checked\"" : "" ?> disabled="disabled" />
          </td><td style="text-align: center">
            <input class="multiDelete" name="deleteContactInformations[]" type="checkbox" value="<?php echo $item->id ?>"/>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

<?php

// Template for new display table rows
$editHtml = '<a href="#">' . image_tag('pencil', array('alt' => __('Edit'), 'style' => 'align: top')) . '</a>';

$rowTemplate = json_encode(<<<value
<tr id="{{$form->getWidgetSchema()->generateName('id')}}">
  <td>
    {{$form->contactPerson->renderName()}}
  </td>
  <td>
    {{$form->primaryContact->renderName()}}
  </td><td style="text-align: right">
    $editHtml <button class="delete-small" name="delete" type="button"/>
  </td>
</tr>

value
);

echo javascript_tag(<<<content

Drupal.behaviors.contactInformation = {
  attach: function (context)
    {
      // Define dialog
      var dialog = new QubitDialog('contactInformationRelation', {
        'displayTable': 'contactInformations',
        'newRowTemplate': $rowTemplate });

      // Add edit button to rows
      jQuery('#contactInformations tr[id]', context)
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
  <div class="section" id="contactInformationRelation">

    <h3><?php echo __('Related contact information') ?></h3>

    <div id="contactInformationRelationTabView" class="yui-navset">

      <ul class="yui-nav">

        <li class="selected"><a href="#contactInformationRelation_Tab1"><em><?php echo __('Main') ?></em></a></li>
        <li><a href="#contactInformationRelation_Tab2"><em><?php echo __('Physical location') ?></em></a></li>
        <li><a href="#contactInformationRelation_Tab3"><em><?php echo __('Other details') ?></em></a></li>

      </ul>

      <div class="yui-content">

        <div id="contactInformationRelation_Tab1">

          <?php echo $form->primaryContact->renderRow() ?>

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

</div>
