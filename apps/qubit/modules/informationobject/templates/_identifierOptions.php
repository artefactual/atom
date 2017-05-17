<div class="section">
  <a href="#" class="identifier-action" id="alternative-identifiers">
    <?php echo __('Add alternative identifier(s)') ?>
  </a>
  <a href="#" class="identifier-action" id="generate-identifier"
    data-generate-identifier-url="<?php echo url_for(array('module' => 'informationobject', 'action' => 'generateIdentifier')) ?>">
    <?php echo __('Generate identifier') ?>
  </a>
</div>

<input name="usingMask" id="using-identifier-mask" type="hidden" value="<?php echo $mask ?>"/>
