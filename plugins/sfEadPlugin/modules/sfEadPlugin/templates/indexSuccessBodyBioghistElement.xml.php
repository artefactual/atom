<?php
  $creators = $$resourceVar->getCreators();
  $events = $$resourceVar->getActorEvents(array('eventTypeId' => QubitTerm::CREATION_ID));
?>

<?php if (0 < count($creators)): ?>
  <?php foreach($events as $date): ?>
    <?php $creator = QubitActor::getById($date->actorId); ?>

    <origination encodinganalog="<?php echo $ead->getMetadataParameter('origination') ?>">
      <?php if ($type = $creator->getEntityTypeId()): ?>
        <?php if (QubitTerm::PERSON_ID == $type): ?>
          <persname><?php echo escape_dc(esc_specialchars($creator->getAuthorizedFormOfName(array('cultureFallback' => true)))) ?></persname>
        <?php endif; ?>
        <?php if (QubitTerm::FAMILY_ID == $type): ?>
          <famname><?php echo escape_dc(esc_specialchars($creator->getAuthorizedFormOfName(array('cultureFallback' => true)))) ?></famname>
        <?php endif; ?>
        <?php if (QubitTerm::CORPORATE_BODY_ID == $type): ?>
          <corpname><?php echo escape_dc(esc_specialchars($creator->getAuthorizedFormOfName(array('cultureFallback' => true)))) ?></corpname>
        <?php endif; ?>
      <?php else: ?>
        <name><?php echo escape_dc(esc_specialchars($creator->getAuthorizedFormOfName(array('cultureFallback' => true)))) ?></name>
      <?php endif; ?>
    </origination>

    <bioghist id="<?php echo 'md5-' . md5(url_for(array($creator, 'module' => 'actor'), true)) ?>" encodinganalog="<?php echo $ead->getMetadataParameter('bioghist') ?>">
      <?php if ($value = $creator->getHistory(array('cultureFallback' => true))): ?>
        <note><p><?php echo escape_dc(esc_specialchars($value)) ?></p></note>
      <?php endif; ?>
      <?php if ($creator->datesOfExistence): ?>
        <date type="existence"><?php echo escape_dc(esc_specialchars($creator->datesOfExistence)) ?></date>
      <?php endif; ?>
    </bioghist>

  <?php endforeach; ?>
<?php endif; ?>
