<div class="section">
  <?php if (empty($hideAltIdButton)) { ?>
    <a href="#" class="identifier-action" id="alternative-identifiers">
      <?php echo __('Add alternative identifier(s)'); ?>
    </a>
  <?php } ?>

  <?php if (empty($hideGenerateButton)) { ?>
    <a href="#" class="identifier-action" id="generate-identifier"
      data-generate-identifier-url="<?php echo url_for(['module' => 'informationobject', 'action' => 'generateIdentifier']); ?>">
      <?php echo __('Generate identifier'); ?>
    </a>
  <?php } ?>
</div>

<?php if (isset($mask)) { ?>
  <input name="usingMask" id="using-identifier-mask" type="hidden" value="<?php echo $mask; ?>"/>
<?php } ?>
