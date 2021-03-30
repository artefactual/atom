  <ListSets>
    <?php foreach ($oaiSets as $set) { ?>
        <set>
           <setSpec><?php echo $set->setSpec(); ?></setSpec>
           <setName><?php echo esc_specialchars(strval($set->getName())); ?></setName>
        </set>
    <?php } ?>
    <?php if ($remaining > 0) { ?>
      <resumptionToken><?php echo $resumptionToken; ?></resumptionToken>
    <?php }?>
  </ListSets>
