
<?php if (0 < count($creators)): ?>
  <?php foreach($events as $date): ?>
    <?php $creator = QubitActor::getById($date->actorId); ?>

    <?php if ($value = $creator->getHistory(array('cultureFallback' => true))): ?>
      <bioghist id="<?php echo 'md5-' . md5(url_for(array($creator, 'module' => 'actor'), true)) ?>" encodinganalog="<?php echo $ead->getMetadataParameter('bioghist') ?>">
        <?php if ($value): ?>
          <note><p><?php echo escape_dc(esc_specialchars($value)) ?></p></note>
        <?php endif; ?>
      </bioghist>
    <?php endif; ?>

  <?php endforeach; ?>
<?php endif; ?>
