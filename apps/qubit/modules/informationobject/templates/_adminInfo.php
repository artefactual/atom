<fieldset class="collapsible collapsed" id="adminInfoArea">

  <legend><?php echo __('Administration area') ?></legend>

  <div class="row">

    <div class="span4">

      <?php echo $form->publicationStatus->label(__('Publication status'))->renderRow() ?>

      <div class="field">
        <h3><?php echo __('Source language') ?></h3>
        <div>
          <?php if (isset($resource->sourceCulture)): ?>
            <?php if ($sf_user->getCulture() == $resource->sourceCulture): ?>
              <?php echo format_language($resource->sourceCulture) ?>
            <?php else: ?>
              <div class="default-translation">
                <?php echo link_to(format_language($resource->sourceCulture), array('sf_culture' => $resource->sourceCulture) + $sf_request->getParameterHolder()->getAll()) ?>
              </div>
            <?php endif; ?>
          <?php else: ?>
            <?php echo format_language($sf_user->getCulture()) ?>
          <?php endif; ?>
        </div>
      </div>

      <?php if (isset($resource->updatedAt)): ?>
        <div class="field">
          <h3><?php echo __('Last updated') ?></h3>
          <div>
            <?php echo $resource->updatedAt ?>
          </div>
        </div>
      <?php endif; ?>

    </div>

    <div class="span4">
      <?php echo $form->displayStandard->label(__('Display standard'))->renderRow() ?>
      <?php echo $form->displayStandardUpdateDescendants->label(__('Assign the new display standard to all its descendants'))->renderRow() ?>
    </div>

  </div>

</fieldset>
