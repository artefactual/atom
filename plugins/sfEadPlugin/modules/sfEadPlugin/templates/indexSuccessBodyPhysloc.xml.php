<?php foreach ($ead->getPhysicalObjects(${$resourceVar}) as $po) { ?>
  <?php if (!empty($po['location'])) { ?>
    <physloc
      id="<?php echo $po['physlocId']; ?>"
    ><?php echo $po['location']; ?></physloc>
  <?php } ?>
  <container
    <?php echo $ead->getEadContainerAttributes($po['object']); ?>
    <?php if (!empty($po['location'])) { ?>
      <?php echo sprintf(' parent="%s"', $po['physlocId']); ?>
    <?php }  ?>
  ><?php echo $po['name']; ?></container>
<?php } // end foreach()?>
