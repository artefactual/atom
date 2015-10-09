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
$editHtml = image_tag('pencil', array('alt' => __('Edit'), 'style' => 'align: top'));

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
            response.resourceDisplay = response.objectDisplay;

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

      // Load primary contact data when a new item is selected.
      // Can't use dialog.loadData() with the donor primary contact url
      // as it loads the data with a different id. The contact data must
      // be obtained first and then update the dialog using the current dialog id
      jQuery('#relatedDonor .yui-ac-input').on('itemSelected', function (e)
        {
          var dataSource = new YAHOO.util.XHRDataSource(e.itemValue + '/donor/primaryContact');
          dataSource.responseType = YAHOO.util.DataSourceBase.TYPE_JSON;
          dataSource.parseJSONData = function (request, response)
            {
              response = dialog.options.relationTableMap.call(dialog, response);

              return { results: [new (function (response)
                {
                  for (name in response)
                  {
                    this[dialog.fieldPrefix + '[' + name + ']'] = response[name];
                  }
                })(response)] };
            }

          dataSource.sendRequest(null, {
            success: function (request, response)
              {
                dialog.updateDialog(dialog.id, response.results[0]);
              } });
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
        <input class="add" type="hidden" data-link-existing="true" value="<?php echo url_for(array('module' => 'donor', 'action' => 'add')) ?> #authorizedFormOfName"/>
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
