<h1><?php echo __('Link physical storage') ?></h1>

<h1 class="label"><?php echo render_title($resource) ?></h1>

<?php echo $form->renderGlobalErrors() ?>

<?php echo $form->renderFormTag(url_for(array($resource, 'module' => 'informationobject', 'action' => 'editPhysicalObjects'))) ?>

  <?php echo $form->renderHiddenFields() ?>

  <?php if (0 < count($relations)): ?>
    <table style="width: 98%;">
      <thead>
        <tr>
          <th colspan="2" style="width: 90%;">
            <?php echo __('Containers') ?>
          </th><th style="width: 5%;">
            <?php echo image_tag('delete', array('align' => 'top', 'class' => 'deleteIcon')) ?>
          </th>
        </tr>
      </thead><tbody>
        <?php foreach ($relations as $item): ?>
          <tr class="related_obj_<?php echo $item->id ?>">
            <td style="width: 90%"><div class="animateNicely">
              <?php echo $item->subject->getLabel() ?>
            </div></td><td style="width: 20px;"><div class="animateNicely">
              <?php echo link_to(image_tag('pencil', array('align' => 'top')), array($item->subject, 'module' => 'physicalobject', 'action' => 'edit')) ?>
            </div></td><td style="width: 20px;"><div class="animateNicely">
              <input class="multiDelete" name="delete_relations[]" type="checkbox" value="<?php echo url_for(array($item, 'module' => 'relation')) ?>"/>
            </div></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>

  <fieldset class="collapsible">

    <legend><?php echo __('Add container links (duplicate links will be ignored)') ?></legend>

    <div class="form-item">
      <?php echo $form->containers->renderLabel() ?>
      <?php echo $form->containers->render(array('class' => 'form-autocomplete')) ?>
      <input class="add" type="hidden" value="<?php echo url_for(array($resource, 'module' => 'informationobject', 'action' => 'editPhysicalObjects')) ?> #name"/>
      <input class="list" type="hidden" value="<?php echo url_for(array('module' => 'physicalobject', 'action' => 'autocomplete')) ?>"/>
    </div>

  </fieldset>

  <fieldset class="collapsible">

    <legend><?php echo __('Or, create a new container') ?></legend>

    <?php echo $form->name->renderRow() ?>

    <?php echo $form->location->renderRow() ?>

    <?php echo $form->type->renderRow() ?>

  </fieldset>

  <div class="actions section">

    <h2 class="element-invisible"><?php echo __('Actions') ?></h2>

    <div class="content">
      <ul class="clearfix links">
        <li><?php echo link_to(__('Cancel'), array($resource, 'module' => 'informationobject')) ?></li>
        <li><input class="form-submit" type="submit" value="<?php echo __('Save') ?>"/></li>
      </ul>
    </div>

  </div>

</form>
