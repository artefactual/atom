<div class="section">
  <?php if (empty($hideAltIdButton)): // Some templates don't have alternative identifiers ?>
    <a href="#" class="identifier-action" id="alternative-identifiers">
      <?php echo __('Add alternative identifier(s)') ?>
    </a>
  <?php endif; ?>

  <?php if (empty($hideGenerateButton)): // Some AtoM data types don't support ID generation ?>
    <a href="#" class="identifier-action" id="generate-identifier"
      data-generate-identifier-url="<?php echo url_for(array('module' => 'informationobject', 'action' => 'generateIdentifier')) ?>">
      <?php echo __('Generate identifier') ?>
    </a>
  <?php endif; ?>
</div>

<?php if (!empty($mask)): ?>
  <input name="usingMask" id="using-identifier-mask" type="hidden" value="<?php echo $mask ?>"/>
<?php endif; ?>
