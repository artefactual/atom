<?php $sf_response->addJavaScript('/vendor/yui/connection/connection-min') ?>
<?php $sf_response->addJavaScript('/vendor/yui/datasource/datasource-min') ?>
<?php $sf_response->addJavaScript('/vendor/yui/container/container-min') ?>
<?php $sf_response->addJavaScript('/vendor/yui/autocomplete/autocomplete-min') ?>
<?php $sf_response->addJavaScript('autocomplete') ?>
<?php $sf_response->addJavaScript('date') ?>
<?php $sf_response->addJavaScript('dialog') ?>
<?php $sf_response->addJavaScript('multiDelete') ?>

<?php use_helper('Javascript') ?>

<?php $suffix =  isset($tableId) ? "_$tableId" : ''; ?>

<div class="section">

  <table class="table table-bordered" id="rightsDisplay<?php echo $suffix ?>">
    <caption>
      <?php echo __('Rights records') ?>
    </caption><thead>
      <tr>
        <th>
          <?php echo __('Act') ?>
        </th><th>
          <?php echo __('Restriction') ?>
        </th><th>
          <?php echo __('Start') ?>
        </th><th>
          <?php echo __('End') ?>
        </th><th style="text-align: center; width: 10%">
          <?php echo image_tag('delete', array('align' => 'top', 'class' => 'deleteIcon')) ?>
        </th>
      </tr>
    </thead><tbody>
      <?php foreach ($rights as $item): ?>
        <tr class="<?php echo 0 == @++$row % 2 ? 'even' : 'odd' ?> related_obj_<?php echo $item->id ?>" id="<?php echo url_for(array($item->object, 'module' => 'right')) ?>">
          <td>
            <?php echo $item->object->act ?>
          </td><td>
            <?php echo $item->object->restriction ? __('Allow') : __('Disallow') ?>
          </td><td>
            <?php echo Qubit::renderDate($item->object->startDate) ?>
          </td><td>
            <?php echo Qubit::renderDate($item->object->endDate) ?>
          </td><td style="text-align: center">
            <input class="multiDelete" name="deleteRights<?php echo $suffix ?>[]" type="checkbox" value="<?php echo url_for(array($item->object, 'module' => 'right')) ?>"/>
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
    {{$form->act->renderName()}}
  </td><td>
    {{$form->restriction->renderName()}}
  </td><td>
    {{$form->startDate->renderName()}}
  </td><td>
    {{$form->endDate->renderName()}}
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
jQuery('#rightsDisplay$suffix tr[id]', context)
  .click(function ()
    {
      dialog.open(this.id);
    })
  .find('td:last')
  .prepend('$editHtml');

editButtonJs;
}

echo javascript_tag(<<<content
Drupal.behaviors.rights$suffix = {
  attach: function (context)
    {
      var basisFormSelectSelector = 'select#editRight${suffix}_basis'.replace('editRight_', 'editRight');

      // Define dialog
      var dialog = new QubitDialog('updateRights$suffix', {
        'displayTable': 'rightsDisplay$suffix',
        'newRowTemplate': $rowTemplate,
        'height': '400px',
        'showEvent': function()
          {
            this.body.scrollTop = 0;

            jQuery('fieldset', this.body)
              .hide()
              .filter('[route="' + jQuery(basisFormSelectSelector, this.table).val() + '"]').show();
          }
        });

      $editButtonJs

      // Hide form areas based in basis select box value
      (function ($)
        {
          $(document).ready(function()
            {
              $(dialog.table).find(basisFormSelectSelector).change(function()
                {
                  $(this)
                    .parent().nextAll('fieldset').hide()
                    .filter('[route="' + $(this).val() + '"]').show();
                });
            });
        })(jQuery);
    } }

content
) ?>

  <!-- NOTE dialog.js wraps this *entire* table in a YUI dialog -->
  <div class="section" id="updateRights<?php echo $suffix ?>">

    <h3><?php echo __('Rights') ?></h3>

    <?php echo $form->act
      ->help(__('The action which is permitted or restricted.'))
      ->renderRow() ?>

    <?php echo $form->restriction
      ->help(__('A condition or limitation on the act.'))
      ->renderRow() ?>

    <?php echo $form->startDate
      ->help(__('The beginning date of the permission granted.'))
      ->renderRow() ?>

    <?php echo $form->endDate
      ->help(__('The ending date of the permission granted. Omit end date if the ending date is unknown.'))
      ->renderRow() ?>

    <div class="form-item">
      <?php echo $form->rightsHolder->renderLabel() ?>
      <?php echo $form->rightsHolder->render(array('class' => 'form-autocomplete')) ?>
      <input class="add" type="hidden" value="<?php echo url_for(array('module' => 'rightsholder', 'action' => 'add')) ?> #authorizedFormOfName"/>
      <input class="list" type="hidden" value="<?php echo url_for(array('module' => 'rightsholder', 'action' => 'autocomplete')) ?>"/>
      <?php echo $form->rightsHolder
        ->help(__('Name of the person(s) or organization(s) which has the authority to grant permissions or set rights restrictions.'))
        ->renderHelp() ?>
    </div>

    <?php echo $form->rightsNote
      ->label(__('Rights note(s)'))
      ->renderRow() ?>

    <?php echo $form->basis
      ->help(__('Basis for the permissions granted or for the restriction of rights'))
      ->renderRow() ?>

    <fieldset route="<?php echo $sf_context->routing->generate(null, array(QubitTerm::getById(QubitTerm::RIGHT_BASIS_COPYRIGHT_ID), 'module' => 'term')) ?>">

      <legend><?php echo __('Copyright information') ?></legend>

      <?php echo $form->copyrightStatus
        ->help(__('A coded designation for the copyright status of the object at the time the rights statement is recorded.'))
        ->renderRow() ?>

      <?php echo $form->copyrightStatusDate->renderRow() ?>

      <?php echo $form->copyrightJurisdiction
        ->help(__('The country whose copyright laws apply.'))
        ->renderRow() ?>

      <?php echo $form->copyrightNote
        ->help(__('Additional information about the copyright status.'))
        ->renderRow() ?>

    </fieldset>

    <fieldset route="<?php echo $sf_context->routing->generate(null, array(QubitTerm::getById(QubitTerm::RIGHT_BASIS_LICENSE_ID), 'module' => 'term')) ?>">

      <legend><?php echo __('License information') ?></legend>

      <?php echo $form->licenseIdentifier
        ->help(__('Can be text value or URI (e.g. to Creative Commons, GNU or other online licenses). Used to identify the granting agreement uniquely within the repository system.'))
        ->renderRow() ?>

      <?php echo $form->licenseTerms
        ->help(__('Text describing the license or agreement by which permission was granted or link to full-text hosted online. This can contain the actual text of the license or agreement or a paraphrase or summary.'))
        ->renderRow() ?>

      <?php echo $form->licenseNote
        ->help(__('Additional information about the license, such as contact persons, action dates, or interpretations. The note may also indicated the location of the license, if it is available online or embedded in the object itself.'))
        ->renderRow() ?>

    </fieldset>

    <fieldset route="<?php echo $sf_context->routing->generate(null, array(QubitTerm::getById(QubitTerm::RIGHT_BASIS_STATUTE_ID), 'module' => 'term')) ?>">

      <legend><?php echo __('Statute information') ?></legend>

      <?php echo $form->statuteJurisdiction
        ->help(__('The country or other political body that has enacted the statute.'))
        ->renderRow() ?>

      <?php echo $form->statuteCitation
        ->help(__('An identifying designation for the statute. Use standard citation form when applicable, e.g. bibliographic citation.'))
        ->renderRow() ?>

      <?php echo $form->statuteDeterminationDate
        ->help(__('Date that the decision to ascribe the right to this statute was made. As context for any future review/re-interpretation.'))
        ->renderRow() ?>

      <?php echo $form->statuteNote
        ->help(__('Additional information about the statute.'))
        ->renderRow() ?>

    </fieldset>

  </div>

</div>
