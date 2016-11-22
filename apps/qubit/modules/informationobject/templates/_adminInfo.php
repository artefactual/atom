<fieldset class="collapsible collapsed" id="adminInfoArea">

  <legend><?php echo __('Administration area') ?></legend>

  <div class="row">

    <div class="span4">

      <div class="field">
        <h3><?php echo __('Source language') ?></h3>
        <div>
          <?php if (isset($resource->sourceCulture)): ?>
            <?php if ($sf_user->getCulture() == $resource->sourceCulture): ?>
              <?php echo format_language($resource->sourceCulture) ?>
            <?php else: ?>
              <div class="default-translation">
                <?php echo link_to(format_language($resource->sourceCulture), array('sf_culture' => $resource->sourceCulture) + $sf_data->getRaw('sf_request')->getParameterHolder()->getAll()) ?>
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
            <?php echo format_date($resource->updatedAt, 'f') ?>
          </div>
        </div>
      <?php endif; ?>

      <div class="field">
        <h3><?php echo __('Source name') ?></h3>
        <div>
          <?php foreach ($resource->fetchAllKeymapEntries() as $keymap): ?>
            <p><?php echo $keymap->sourceName ?></p>
          <?php endforeach ?>
        </div>
      </div>
    </div>

    <div class="span4">
      <?php echo $form->displayStandard->label(__('Display standard'))->renderRow() ?>
      <?php echo $form->displayStandardUpdateDescendants->label(__('Make this selection the new default for existing children'))->renderRow() ?>
    </div>

  </div>

</fieldset>
