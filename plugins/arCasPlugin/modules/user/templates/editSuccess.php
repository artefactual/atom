<?php decorate_with('layout_1col.php') ?>
<?php use_helper('Javascript') ?>

<?php slot('title') ?>
  <h1><?php echo __('User %1%', array('%1%' => render_title($resource))) ?></h1>
<?php end_slot() ?>

<?php slot('content') ?>

  <?php echo $form->renderGlobalErrors(); ?>

  <?php if (isset($sf_request->getAttribute('sf_route')->resource)): ?>
    <?php echo $form->renderFormTag(url_for(array($resource, 'module' => 'user', 'action' => 'edit')), array('id' => 'editForm')) ?>
  <?php else: ?>
    <?php echo $form->renderFormTag(url_for(array('module' => 'user', 'action' => 'add')), array('id' => 'editForm')) ?>
  <?php endif; ?>

    <section id="content">

      <?php if ($sf_user->user != $resource): ?>
        <fieldset class="collapsible" id="basicInfo">

          <legend><?php echo __('Basic info') ?></legend>
          
            <?php echo $form->active
              ->label(__('Active'))
              ->renderRow() ?>

        </fieldset> <!-- /#basicInfo -->
      <?php endif; ?>

      <fieldset class="collapsible" id="groupsAndPermissions">

        <legend><?php echo __('Access control')?></legend>

        <?php echo $form->groups
          ->label(__('User groups'))
          ->renderRow(array('class' => 'form-autocomplete')) ?>

        <?php echo $form->translate
          ->label(__('Allowed languages for translation'))
          ->renderRow(array('class' => 'form-autocomplete')) ?>

        <?php if ($restEnabled): ?>
          <?php echo $form->restApiKey
            ->label(__('REST API access key'. ((isset($restApiKey)) ? ': <code>'. $restApiKey .'</code>' : '')))
            ->renderRow() ?>
        <?php endif; ?>

        <?php if ($oaiEnabled): ?>
          <?php echo $form->oaiApiKey
            ->label(__('OAI-PMH API access key'. ((isset($oaiApiKey)) ? ': <code>'. $oaiApiKey .'</code>' : '')))
            ->renderRow() ?>
        <?php endif; ?>

      </fieldset> <!-- /#groupsAndPermissions -->

    </section>

    <section class="actions">
      <ul>
        <?php if (isset($sf_request->getAttribute('sf_route')->resource)): ?>
          <li><?php echo link_to(__('Cancel'), array($resource, 'module' => 'user'), array('class' => 'c-btn')) ?></li>
          <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Save') ?>"/></li>
        <?php else: ?>
          <li><?php echo link_to(__('Cancel'), array('module' => 'user', 'action' => 'list'), array('class' => 'c-btn')) ?></li>
        <?php endif; ?>
      </ul>
    </section>

  </form>

<?php end_slot() ?>
