<?php use_helper('Date'); ?>

<h1><?php echo __('Browse Cart Items %1%', ['%1%' => sfConfig::get('app_ui_label_cart')]); ?></h1>

<table class="table table-bordered sticky-enabled"> 
  <thead>
	<style>
		blink {
			color: #2d38be;
			font-size: 15px;
			font-weight: bold;
		}
	</style>
    <tr>
      <th>
        <?php echo __('Archival Description'); ?>
      </th>
	  <th>
        <?php echo __('Level of Description'); ?>
      </th>
	  <th>
        <?php echo __('Remove item'); ?>
      </th>
    </tr>
  </thead><tbody>
  	<?php echo $form->renderGlobalErrors(); ?>
	<?php echo $form->renderFormTag(url_for([$resource, 'module' => 'cart', 'action' => 'browse'])); ?>
	<?php echo $form->renderHiddenFields(); ?>
	<?php if (0 == $pager->getNbResults()) { ?>
			<tr>
				<td colspan=3>
					<blink><b><?php echo 'Cart is empty'; ?></b></blink>
				</td>
			</tr>
			
		<?php }	?>
    <?php foreach ($pager->getResults() as $item) { 	?>
	  <tr class="<?php echo 0 == @++$row % 2 ? 'even' : 'odd'; ?>">
		<td>
			<?php $informationObjectsCart = QubitInformationObject::getById($item->archivalDescriptionId); ?>
			<?php if (isset($informationObjectsCart->identifier)) { ?> <?php echo link_to($informationObjectsCart, [$informationObjectsCart, 'module' => 'informationobject']); ?> <?php } ?>
		</td>
		<td>
			
			<?php echo $informationObjectsCart->levelOfDescription; ?>
		</td>
		<td>
			<?php echo link_to('Remove', [$this->resource, 'module' => 'cart', 'action' => 'removeCart', 'id' => $item->id]); ?>
		</td>
	  </tr>
    <?php } ?>
  </tbody>
</table>
<?php echo get_partial('default/pager', ['pager' => $pager]); ?>
   <section id="content">
		<fieldset class="collapsible">
		<fieldset class="collapsible">
		<legend><?php echo __('Request To Publish area'); ?></legend>
			<tr>
				<td colspan=3>
				</td>
			<tr>
				<td colspan=3>
					<?php echo $form->rtp_name->label(__('Name'))->renderRow(); ?>
				</td>
			</tr>
			<tr>
				<td colspan=3>
					<?php echo $form->rtp_surname->label(__('Surname'))->renderRow(); ?>
				</td>
			</tr>
			<tr>
				<td colspan=3>
					<?php echo $form->rtp_phone->label(__('Phone Number'))->renderRow(); ?>
				</td> 
			<tr>
				<td colspan=3>
					<?php echo $form->rtp_email->label(__('e-Mail Address'))->renderRow(); ?>
				</td>
			</tr>
			<tr>
				<td colspan=3>
					<?php echo $form->rtp_institution->label(__('Institution'))->renderRow(); ?>
				</td>
			</tr>
			<tr>
				<td colspan=3>
					<?php echo $form->rtp_planned_use->label(__('Planned use'))->renderRow(); ?>
				</td>
			</tr>
			<tr>
				<td colspan=3>
					<?php echo $form->rtp_need_image_by->label(__('Need image/s by'))->renderRow(); ?>
				</td>
			</tr>
			<tr>
				<td colspan=3>
					<?php echo $form->rtp_motivation->label(__('Motivation'))->renderRow(); ?>
				</td>
			</tr>
		</fieldset> 
    </section>
	<section class="actions">
		<table width="100%" cellspacing=0 border="0" cellpadding="0" align="left" summary="">
		  <ul class="clearfix links">
			<li><?php echo link_to(__('Back to List'), ['module' => 'requesttopublish', 'action' => 'browse'], ['title' => __('Back to list'), 'class' => 'c-btn']); ?></li>
			<?php if ($resource->unique_identifier != $this->context->user->getAttribute('user_id') && $pager->getNbResults() > 0) { ?>
					<li><input class="c-btn c-btn-submit" type="submit" id="requesttopublishcart"  value="<?php echo __('Submit'); ?>"/></li>
			<?php } ?>
		  </ul>
		</table>
	</section>

