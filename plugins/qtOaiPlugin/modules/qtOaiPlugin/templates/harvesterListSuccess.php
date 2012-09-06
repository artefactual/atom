<h1><?php echo __('OAI-PMH - harvested sites') ?></h1>

<?php if (0 < count($repositories)): ?>
<table class="sticky-enabled">
  <thead>
    <tr>
      <th><?php echo __('Repository') ?></th>
      <th><?php echo __('Harvest') ?></th>
      <th><?php echo __('Delete') ?></th>
    </tr>
  </thead>
  <tbody>
  <?php foreach($repositories as $rep): ?>
    <tr>
      <td>
        <div>
          <a href="<?php echo $rep->getUri();?>?verb=Identify"><?php echo $rep->getName(); ?></a><br>Last Harvest: <?php $harvest = QubitOaiHarvest::getLastHarvestByID($rep->id); echo $harvest->getLastHarvest();;?>
        </div>
      </td><td>
      <?php foreach($rep->getOaiHarvests() as $harvestJob): ?>
        <?php echo link_to(__('Harvest'), array('module' => 'qtOaiPlugin', 'action' => 'harvesterHarvest', 'id' => $harvestJob->id)) ?>
      <?php endforeach; ?>
      </td><td>
        <?php echo link_to(__('Delete'), array('module' => 'qtOaiPlugin', 'action' => 'deleteRepository'), array('class' => 'delete')) ?>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>

<fieldset class="collapsible" id="addOaiRepository">
  <legend><?php echo __('Add new repository') ?></legend>

  <form action="<?php echo url_for(array('module' => 'qtOaiPlugin', 'action' => 'harvesterList')) ?>" method="post">
    <?php echo $form->renderGlobalErrors() ?>

    <?php echo $form->uri
      ->help(__('Enter a valid URI (e.g. http://www.example.com/oai) for an OAI-PMH repository'))
      ->renderRow() ?>
    <input class="form-submit" type="submit" value="<?php echo __('Save') ?>"/>
  </form>
</fieldset>
