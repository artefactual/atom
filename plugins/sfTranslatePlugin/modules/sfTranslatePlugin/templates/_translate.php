<?php use_helper('Javascript') ?>
<?php use_helper('Text') ?>

<div id="l10n-client">

  <div class="labels">
    <span id="l10n-client-hide">X</span>
    <span id="l10n-client-show"><?php echo __('Translate user interface') ?></span>
    <div class="lbl strings">
      <h2><?php echo __('Page text') ?></h2>
    </div>
    <div class="lbl source">
      <h2><?php echo __('Source') ?></h2>
    </div>
    <div class="lbl translation">
      <h2><?php echo __('%language% translation', array('%language%' => format_language($sf_user->getCulture()))) ?></h2>
    </div>
  </div>

  <div id="l10n-client-string-select">
    <ul class="string-list">
      <?php foreach ($sf_data->getRaw('messages') as $source => $target): ?>
        <li><?php echo truncate_text(empty($target) ? $source : $target) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
  <div id="l10n-client-string-editor">
    <?php echo form_tag('sfTranslatePlugin/translate', array('id' => 'l10n-client-form')) ?>
      <div class="source">
      </div>
      <div class="translation">
      </div>
      <input class="form-submit" type="submit" value="<?php echo __('Save translation') ?>"/>
    </form>
  </div>
</div>
<?php $l10nSourceMessages = json_encode(array_keys($sf_data->getRaw('messages'))) ?>
<?php $l10nTargetMessages = json_encode(array_values($sf_data->getRaw('messages'))) ?>
<?php echo javascript_tag(<<<EOF
jQuery.extend(Drupal, {
  l10nSourceMessages: $l10nSourceMessages,
  l10nTargetMessages: $l10nTargetMessages});
EOF
) ?>
