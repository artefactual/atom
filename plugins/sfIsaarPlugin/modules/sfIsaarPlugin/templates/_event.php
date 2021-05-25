<?php $sf_response->addJavaScript('date', 'last'); ?>
<?php $sf_response->addJavaScript('/vendor/yui/datasource/datasource-min', 'last'); ?>
<?php $sf_response->addJavaScript('/vendor/yui/container/container-min', 'last'); ?>
<?php $sf_response->addJavaScript('dialog', 'last'); ?>
<?php $sf_response->addJavaScript('multiDelete', 'last'); ?>
<?php $sf_response->addJavaScript('pager', 'last'); ?>
<?php $sf_response->addJavaScript('/plugins/sfIsaarPlugin/js/actorEvents', 'last'); ?>

<?php use_helper('Javascript'); ?>

<div class="section">

  <table id="relatedEvents" class="table table-bordered">
    <caption>
      <?php echo __('Related resources'); ?>
    </caption><thead>
      <tr>
        <th style="width: 35%">
          <?php echo __('Title'); ?>
        </th><th style="width: 20%">
          <?php echo __('Relationship'); ?>
        </th><th style="width: 25%">
          <?php echo __('Dates'); ?>
        </th><th style="text-align: center; width: 10%">
        </th>
      </tr>
    </thead><tbody id="actorEvents">
    </tbody>
  </table>

  <a id="actorEventsNextButton" class="btn btn-small pull-right invisible" data-slug="<?php echo $resource->slug; ?>">Show more</a>

  <!-- Template for edit button -->
  <div id="editButtonTemplate" style="display: none">
    <?php echo image_tag('pencil', ['alt' => __('Edit'), 'style' => 'align: top']); ?>
  </div>

  <!-- Template for new rows created by YUI dialog -->
  <table style="display: none">
    <tbody id="dialogNewRowTemplate">
      <tr>
        <td>
          {<?php echo $form->informationObject->renderName(); ?>}
        </td><td>
          {<?php echo $form->type->renderName(); ?>}
        </td><td>
          {<?php echo $form->date->renderName(); ?>}
        </td><td style="text-align: right">
          <?php echo image_tag('pencil', ['alt' => __('Edit'), 'style' => 'align: top']); ?> <button class="delete-small" name="delete" type="button"/>
        </td>
      </tr>
    </tbody>
  </table>

  <!-- NOTE dialog.js wraps this *entire* table in a YUI dialog -->
  <div class="date section" id="resourceRelation">

    <div class="messages error" id="resourceRelationError" style="display: none">
      <ul>
        <li><?php echo __('Please complete all required fields.'); ?></li>
      </ul>
    </div>

    <div class="form-item">
      <?php echo $form->informationObject
          ->label(__('Title of related resource'))
          ->renderLabel(); ?>
      <?php echo $form->informationObject->render(['class' => 'form-autocomplete']); ?>
      <input class="list" type="hidden" value="<?php echo url_for(['module' => 'informationobject', 'action' => 'autocomplete']); ?>"/>
      <?php echo $form->informationObject
          ->help(__('"Provide the unique identifiers/reference codes and/or titles for the related resources." (ISAAR 6.1) Select the title from the drop-down menu; enter the identifier or the first few letters to narrow the choices.'))
          ->renderHelp(); ?>
    </div>

    <?php echo $form->type
        ->help(__('"Describe the nature of the relationships between the corporate body, person or family and the related resource." (ISAAR 6.3) Select the type of relationship from the drop-down menu; these values are drawn from the Event Types taxonomy.'))
        ->label(__('Nature of relationship'))
        ->renderRow(); ?>

    <?php echo $form->resourceType
        ->help(__('"Identify the type of related resources, e.g. Archival materials (fonds, record series, etc), archival description, finding aid, monograph, journal article, web site, photograph, museum collection, documentary film, oral history recording." (ISAAR 6.2) In the current version of the software, Archival material is provided as the only default value.'))
        ->label(__('Type of related resource'))
        ->renderRow(['disabled' => 'true', 'class' => 'disabled']); ?>

    <?php echo $form->date
        ->help(__('"Provide any relevant dates for the related resources and/or the relationship between the corporate body, person or family and the related resource." (ISAAR 6.4) Enter the date as you would like it to appear in the show page for the authority record, using qualifiers and/or typographical symbols to express uncertainty if desired.'))
        ->renderRow(); ?>

    <?php echo $form->startDate->renderRow(); ?>

    <?php echo $form->endDate->renderRow(); ?>

  </div>

</div>
