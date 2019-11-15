<?php decorate_with('layout_1col') ?>

<?php slot('title') ?>

  <?php if (!empty($sendMessageHtml)): ?>
    <?php echo __($sendMessageHtml) ?>
  <?php endif; ?>

<?php end_slot() ?>

<?php slot('content') ?>

  <form id="sendForm" action="<?php echo $sendUrl ?>" method="<?php echo $sendHttpMethod ?>">

    <input name="base_url" type="hidden" value="<?php echo $siteBaseUrl ?>" />

    <?php foreach ($classSlugFieldNames as $className => $fieldName): ?>
      <input name="<?php echo $fieldName ?>" type="hidden" value="<?php echo esc_entities($serializedSlugs->getRaw($className)) ?>" />
    <?php endforeach; ?>

    <input id="sendFormSubmit" type="submit" value="<?php echo __('Send') ?>">
  </form>

<?php end_slot() ?>
