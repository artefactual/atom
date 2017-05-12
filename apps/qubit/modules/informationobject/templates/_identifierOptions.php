<div class="section">
  <?php if (!isset($hideAltIdButton)): // Some templates don't have alt id support yet ?>
    <a href="#" class="identifier-action" id="alternative-identifiers">
      <?php echo __('Add alternative identifier(s)') ?>
    </a>
  <?php endif; ?>

  <a href="#" class="identifier-action" id="generate-identifier"
    data-generate-identifier-url="<?php echo url_for(array('module' => 'informationobject', 'action' => 'generateIdentifier')) ?>">
    <?php echo __('Generate identifier') ?>
  </a>
</div>

<input name="usingMask" id="using-identifier-mask" type="hidden" value="<?php echo $mask ?>"/>
