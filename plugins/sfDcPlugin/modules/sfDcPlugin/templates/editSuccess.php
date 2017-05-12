<?php decorate_with('layout_2col.php') ?>
<?php use_helper('Date') ?>

<?php slot('sidebar') ?>

  <?php include_component('repository', 'contextMenu') ?>

<?php end_slot() ?>

<?php slot('title') ?>

  <h1><?php echo render_title($dc) ?></h1>

  <?php if (isset($sf_request->source)): ?>
    <div class="messages status">
      <?php echo __('This is a duplicate of record %1%', array('%1%' => $sourceInformationObjectLabel)) ?>
    </div>
  <?php endif; ?>

<?php end_slot() ?>

<?php slot('content') ?>

  <?php echo $form->renderGlobalErrors() ?>

  <?php if (isset($sf_request->getAttribute('sf_route')->resource)): ?>
    <?php echo $form->renderFormTag(url_for(array($resource, 'module' => 'informationobject', 'action' => 'edit')), array('id' => 'editForm')) ?>
  <?php else: ?>
    <?php echo $form->renderFormTag(url_for(array('module' => 'informationobject', 'action' => 'add')), array('id' => 'editForm')) ?>
  <?php endif; ?>

    <?php echo $form->renderHiddenFields() ?>

    <div id="content">

      <fieldset class="collapsible" id="elementsArea">

        <legend><?php echo __('Elements area') ?></legend>

        <?php echo $form->identifier
          ->help(__('The unambiguous reference code used to uniquely identify this resource.'))
          ->label(__('Identifier').' <span class="form-required" title="'.__('This is a mandatory element.').'">*</span>')
          ->renderRow() ?>

        <?php echo get_partial('informationobject/identifierOptions', array('mask' => $mask, 'hideAltIdButton' => true)) ?>

        <?php echo render_field($form->title
          ->help(__('The name given to this resource.'))
          ->label(__('Title').' <span class="form-required" title="'.__('This is a mandatory element.').'">*</span>'), $resource) ?>

        <section>

          <h3><?php echo __('Names and dates') ?></h3>

          <?php echo get_partial('dcNames', $sf_data->getRaw('dcNamesComponent')->getVarHolder()->getAll()) ?>

          <?php echo get_partial('dcDates', $sf_data->getRaw('dcDatesComponent')->getVarHolder()->getAll()) ?>

        </section>

        <div class="form-item">
          <?php echo $form->subjectAccessPoints
            ->label(__('Subject'))
            ->renderLabel() ?>
          <?php echo $form->subjectAccessPoints->render(array('class' => 'form-autocomplete')) ?>
          <?php if (QubitAcl::check(QubitTaxonomy::getById(QubitTaxonomy::SUBJECT_ID), 'createTerm')): ?>
            <input class="add" type="hidden" data-link-existing="true" value="<?php echo url_for(array('module' => 'term', 'action' => 'add', 'taxonomy' => url_for(array(QubitTaxonomy::getById(QubitTaxonomy::SUBJECT_ID), 'module' => 'taxonomy')))) ?> #name"/>
          <?php endif; ?>
          <input class="list" type="hidden" value="<?php echo url_for(array('module' => 'term', 'action' => 'autocomplete', 'taxonomy' => url_for(array(QubitTaxonomy::getById(QubitTaxonomy::SUBJECT_ID), 'module' => 'taxonomy')))) ?>"/>
          <?php echo $form->subjectAccessPoints
            ->help(__('The topic of the resource. Search for an existing term in the Subject taxonomy by typing the first few characters of the term name. Alternatively, type a new name to create and link to a new subject term.'))
            ->renderHelp() ?>
        </div>

        <?php echo render_field($form->scopeAndContent
          ->help(__('An abstract, table of contents or description of the resource\'s scope and contents.'))
          ->label(__('Description')), $resource, array('class' => 'resizable')) ?>

        <?php echo $form->type
          ->help(__('<p>The nature or genre of the resource.</p><p>Assign as many types as applicable. The <em>Type</em> options are limited to the DCMI Type vocabulary.</p><p>Assign the <em>Collection</em> value if this resource is the top-level for a set of lower-level (child) resources.</p><p>Please note: if this resource is linked to a digital object, the <em>image</em>, <em>text</em>, <em>sound</em> or <em>moving image</em> types are added automatically upon output, so do not duplicate those values here.</p>'))
          ->renderRow() ?>

        <section>

          <h3><?php echo __('Child levels (if describing a collection)') ?></h3>

          <table class="table table-bordered multiRow">
            <thead>
              <tr>
                <th style="width: 20%">
                  <?php echo __('Identifier') ?>
                </th><th style="width: 80%">
                  <?php echo __('Title') ?>
                </th>
              </tr>
            </thead><tbody>
              <tr>
                <td>
                  <input type="text" name="updateChildLevels[0][identifier]"/>
                </td><td>
                  <input type="text" name="updateChildLevels[0][title]"/>
                </td>
              </tr>
            </tbody>
          </table>

          <div class="description">
            <?php echo __('<p><strong>Child levels</strong><br/>Use these two fields to add lower levels to a collection level description. Click <em>Add new</em> to create as many child levels as necessary.</p><p>These fields can also be used to add any number of intermediate levels of description (e.g. series, file, etc) between the top and bottom levels in a descriptive hierarchy. Use the hierarchy treeview to re-order hierarchy levels as necessary.</p><p><em>Identifier</em>: The unambiguous reference code used to uniquely identify the child-level resource.</p><p><em>Title</em>: The name given to the child-level resource.</p>') ?>
          </div>

        </section>

        <?php echo render_field($form->extentAndMedium
          ->help(__('<p>The file format, physical medium, or dimensions of the resource.</p><p>Please note: if this resource is linked to a digital object, the Internet Media Types (MIME) will be added automatically upon output, so don\'t duplicate those values here.</p>'))
          ->label(__('Format')), $resource, array('class' => 'resizable')) ?>

        <?php echo render_field($form->locationOfOriginals
          ->help(__('Related material(s) from which this resource is derived.'))
          ->label(__('Source')), $resource, array('class' => 'resizable')) ?>

        <?php echo $form->language
          ->help(__('Language(s) of this resource.'))
          ->renderRow(array('class' => 'form-autocomplete')) ?>

        <div class="form-item">
          <?php echo $form->repository
            ->label(__('Relation (isLocatedAt)').' <span class="form-required" title="'.__('This is a mandatory element for this resource or one of its higher descriptive levels (if part of a collection hierarchy).').'">*</span>')
            ->renderLabel() ?>
          <?php echo $form->repository->render(array('class' => 'form-autocomplete')) ?>
          <input class="add" type="hidden" data-link-existing="true" value="<?php echo url_for(array('module' => 'repository', 'action' => 'add')) ?> #authorizedFormOfName"/>
          <input class="list" type="hidden" value="<?php echo url_for($sf_data->getRaw('repoAcParams')) ?>"/>
          <?php echo $form->repository
            ->help(__('<p>The name of the organization which has custody of the resource.</p><p>Search for an existing name in the organization records by typing the first few characters of the name. Alternatively, type a new name to create and link to a new organization record.</p>'))
            ->renderHelp() ?>
        </div>

        <div class="form-item">
          <?php echo $form->placeAccessPoints
            ->label(__('Coverage (spatial)'))
            ->renderLabel() ?>
          <?php echo $form->placeAccessPoints->render(array('class' => 'form-autocomplete')) ?>
          <?php if (QubitAcl::check(QubitTaxonomy::getById(QubitTaxonomy::PLACE_ID), 'createTerm')): ?>
            <input class="add" type="hidden" data-link-existing="true" value="<?php echo url_for(array('module' => 'term', 'action' => 'add', 'taxonomy' => url_for(array(QubitTaxonomy::getById(QubitTaxonomy::PLACE_ID), 'module' => 'taxonomy')))) ?> #name"/>
          <?php endif; ?>
          <input class="list" type="hidden" value="<?php echo url_for(array('module' => 'term', 'action' => 'autocomplete', 'taxonomy' => url_for(array(QubitTaxonomy::getById(QubitTaxonomy::PLACE_ID), 'module' => 'taxonomy')))) ?>"/>
          <?php echo $form->placeAccessPoints
            ->help(__('<p>The name of a place or geographic area which is a topic of the resource or relevant to its jurisdiction.</p><p>Search for an existing term in the Place taxonomy by typing the first few characters of the place name. Alternatively, type a new name to create and link to a new place.</p><p>Please note: if you entered a place of creation, publication or contribution that will be output automatically, so don’t repeat that place name here.</p>'))
            ->renderHelp() ?>
        </div>

        <?php echo render_field($form->accessConditions
          ->help(__('Information about rights held in and over the resource (e.g. copyright, access conditions, etc.).'))
          ->label(__('Rights')), $resource, array('class' => 'resizable')) ?>

      </fieldset> <!-- #elementsArea -->

      <?php echo get_partial('informationobject/adminInfo', array('form' => $form, 'resource' => $resource)) ?>

    </div>

    <?php echo get_partial('informationobject/editActions', array('resource' => ($parent !== null ? $parent : $resource))) ?>

  </form>

<?php end_slot() ?>
