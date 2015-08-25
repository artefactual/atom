<?php $sf_response->addJavaScript('date') ?>
<?php $sf_response->addJavaScript('/vendor/yui/datasource/datasource-min') ?>
<?php $sf_response->addJavaScript('/vendor/yui/container/container-min') ?>
<?php $sf_response->addJavaScript('dialog') ?>
<?php $sf_response->addJavaScript('multiDelete') ?>

<?php use_helper('Javascript') ?>

<div class="section">

  <table id="relatedEntities" class="table table-bordered">
    <caption>
      <?php echo __('Related corporate bodies, persons or families') ?>
    </caption><thead>
      <tr>
        <th style="width: 20%">
          <?php echo __('Name') ?>
        </th><th style="width: 15%">
          <?php echo __('Category') ?>
        </th><th style="width: 15%">
          <?php echo __('Type') ?>
        </th><th style="width: 15%">
          <?php echo __('Dates') ?>
        </th><th style="width: 25%">
          <?php echo __('Description') ?>
        </th><th style="text-align: center; width: 10%">
        </th>
      </tr>
    </thead><tbody>
      <?php foreach ($resource->getActorRelations() as $item): ?>
        <tr class="<?php echo 0 == @++$row % 2 ? 'even' : 'odd' ?> related_obj_<?php echo $item->id ?>" id="<?php echo url_for(array($item, 'module' => 'relation')) ?>">
          <td>
            <?php if ($resource->id == $item->objectId): ?>
              <?php echo render_title($item->subject) ?>
            <?php else: ?>
              <?php echo render_title($item->object) ?>
            <?php endif; ?>
          </td><td>
            <?php if ($item->type->parentId == QubitTerm::ROOT_ID): ?>
              <?php echo $item->type ?>
            <?php else: ?>
              <?php echo $item->type->parent ?>
            <?php endif; ?>
          </td><td>
            <?php if ($item->type->parentId != QubitTerm::ROOT_ID): ?>
              <?php if ($resource->id != $item->objectId): ?>
                <?php echo $item->type.' '.render_title($resource) ?>
              <?php elseif (0 < count($converseTerms = QubitRelation::getBySubjectOrObjectId($item->type->id, array('typeId' => QubitTerm::CONVERSE_TERM_ID)))): ?>
                <?php echo $converseTerms[0]->getOpposedObject($item->type).' '.render_title($resource) ?>
              <?php endif; ?>
            <?php endif; ?>
          </td><td>
            <?php echo Qubit::renderDateStartEnd($item->date, $item->startDate, $item->endDate) ?>
          </td><td>
            <?php echo $item->description ?>
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
    {{$form->subType->renderName()}}
  </td><td>
    {{$form->date->renderName()}}
  </td><td>
    {{$form->description->renderName()}}
  </td><td style="text-align: right">
    $editHtml <button class="delete-small" name="delete" type="button"/>
  </td>
</tr>

value
);

$url = url_for($resource);
$actorTitle = esc_entities($resource->__toString());

echo javascript_tag(<<<content
Drupal.behaviors.relatedAuthorityRecord = {
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
      var dialog = new QubitDialog('actorRelation', {
        'displayTable': 'relatedEntities',
        'handleFieldRender': handleFieldRender,
        'newRowTemplate': $rowTemplate,
        'relationTableMap': function (response)
          {
            response.resource = response.object;
            if ('$url' === response.resource)
            {
              response.resource = response.subject;
              response.subType = response.converseSubType;
            }

            response.actor = ' $actorTitle';

            return response;
          } });

      // Add edit button to rows
      jQuery('#relatedEntities tr[id]', context)
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
  <div class="date section" id="actorRelation">

    <h3><?php echo __('Related corporate body, person or family') ?></h3>

    <div>

      <div class="form-item">
        <?php echo $form->resource
          ->label(__('Authorized form of name'))
          ->renderLabel() ?>
        <?php echo $form->resource->render(array('class' => 'form-autocomplete')) ?>
        <input class="list" type="hidden" value="<?php echo url_for(array('module' => 'actor', 'action' => 'autocomplete')) ?>"/>
        <?php echo $form->resource
          ->help(__('"Record the authorized form of name and any relevant unique identifiers, including the authority record identifier, for the related entity." (ISAAR 5.3.1) Select the name from the drop-down menu; enter the first few letters to narrow the choices.'))
          ->renderHelp() ?>
      </div>

      <?php echo $form->type
        ->help(__('"Purpose: To identify the general category of relationship between the entity being described and another corporate body, person or family." (ISAAR 5.3.2). Select a category from the drop-down menu: hierarchical, temporal, family or associative.'))
        ->label(__('Category of relationship'))
        ->renderRow() ?>

      <div class="form-item">
        <?php echo $form->subType
          ->label(__('Relationship type'))
          ->renderLabel() ?>
        <?php echo $form->subType->render(array('class' => 'form-autocomplete', 'disabled' => 'true')) ?>
        <input class="list" type="hidden" value="<?php echo url_for(array('module' => 'term', 'action' => 'autocomplete', 'taxonomy' => url_for(array(QubitTaxonomy::getById(QubitTaxonomy::ACTOR_RELATION_TYPE_ID), 'module' => 'taxonomy')), 'addWords' => render_title($resource))) ?>"/>
        <?php echo $form->subType
          ->help(__('"Select a descriptive term from the drop-down menu to clarify the type of relationship between these two actors."'))
          ->renderHelp() ?>
      </div>

      <?php echo $form->description
        ->help(__('"Record a precise description of the nature of the relationship between the entity described in this authority record and the other related entity....Record in the Rules and/or conventions element (5.4.3) any classification scheme used as a source of controlled vocabulary terms to describe the relationship. A narrative description of the history and/or nature of the relationship may also be provided here." (ISAAR 5.3.3). Note that the text entered in this field will also appear in the related authority record.'))
        ->label(__('Description of relationship'))
        ->renderRow() ?>

      <?php echo $form->date
        ->help(__('"Record when relevant the commencement date of the relationship or succession date and, when relevant, the cessation date of the relationship." (ISAAR 5.3.4) Enter the date as you would like it to appear in the show page for the authority record, using qualifiers and/or typographical symbols to express uncertainty if desired.'))
        ->renderRow() ?>

      <?php echo $form->startDate->renderRow() ?>

      <?php echo $form->endDate->renderRow() ?>

    </div>

  </div>

</div>
