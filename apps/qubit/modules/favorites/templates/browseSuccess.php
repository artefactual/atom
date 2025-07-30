<?php use_helper('Date') ?>

<h1><?php echo __('Browse Favorite Items %1%', array('%1%' => sfConfig::get('app_ui_label_cart'))) ?></h1>

<table class="table table-bordered sticky-enabled"> 
  <thead>
    <tr>
      <th>
        <?php echo __('Archival Description') ?>
      </th>
	  <th>
        <?php echo __('Level of Description') ?>
      </th>
	  <th>
        <?php echo __('Remove item') ?>
      </th>
    </tr>
  </thead><tbody>
    <?php foreach ($pager->getResults() as $item): 	?>
	  <tr class="<?php echo 0 == @++$row % 2 ? 'even' : 'odd' ?>">
		<td>
			<?php $informationObjectsCart = QubitInformationObject::getById($item->archivalDescriptionId); ?>
			<?php if (isset($informationObjectsCart->identifier)) { ?> <?php echo link_to($informationObjectsCart, array($informationObjectsCart, 'module' => 'informationobject')) ?> <?php } ?>
		</td>
		<td>
			<?php echo $informationObjectsCart->levelOfDescription ?>
		</td>
		<td>
			<?php echo link_to("Remove" , array($this->resource, 'module' => 'favorites', 'action' => 'removeFavorites', 'id' => $item->id)) ?>
		</td>
	  </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php echo get_partial('default/pager', array('pager' => $pager)) ?>

 <section class="actions">
      <ul>
		<li><input class="c-btn c-btn-submit" type="button" onclick="history.back();" value="Back"></li>
      </ul>
  </section>

