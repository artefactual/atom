<?php decorate_with('layout_1col.php') ?>

<?php slot('title') ?>
	<h1 class="multiline"> <!--changed from 'label'-->
		<?php echo render_title(__('Feedback')) ?>
		<br>
		<br>
		<?php echo render_title($resource) ?>
	</h1>
<?php end_slot() ?>

<?php slot('content') ?>
	<body>
	<?php echo $form->renderGlobalErrors() ?>
	<?php echo $form->renderFormTag(url_for(array($resource, 'module' => 'feedback', 'action' => 'editFeedback'))) ?>
	<?php echo $form->renderHiddenFields() ?>
    <section id="content">
		<fieldset class="collapsible">
		<legend><?php echo __('Identification area') ?></legend>
			<table width="100%" cellspacing=0 border="0" cellpadding="0" align="left" summary="">
				<div class="content">
					<tr>
						<td colspan=1>
							<?php echo $form->name->renderRow(array('size' => 50, 'readonly'=>'true'), 'Name of Collection/Item')  ?>
						</td>
						<td colspan=2>
							<?php echo $form->identifier->renderRow(array('size' => 50, 'readonly'=>'true'), 'Identifier')  ?>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo $form->unique_identifier->renderRow(array('readonly'=>'true'), 'Unique Identifier') ?>
						</td>
						<td>
						</td>
					</tr>
				</div>
			</table>
		</fieldset>
		<fieldset class="collapsible">
		<legend><?php echo __('Feedback area') ?></legend>
			<tr>
				<td colspan=3>
					<?php echo $form->remarks->label(__('Remarks/Feedback/Comments'))->renderRow(); ?>
				</td>
			</tr>
			<tr>
				<td colspan=3>
				</td>
			<tr>
				<td colspan=3>
					<?php echo $form->feed_name->label(__('Name'))->renderRow(); ?>
				</td>
			</tr>
			<tr>
				<td colspan=3>
					<?php echo $form->feed_surname->label(__('Surname'))->renderRow(); ?>
				</td>
			</tr>
			<tr>
				<td colspan=3>
					<?php echo $form->feed_phone->label(__('Phone Number'))->renderRow(); ?>
				</td> 
			<tr>
				<td colspan=3>
					<?php echo $form->feed_email->label(__('e-Mail Address'))->renderRow(); ?>
				</td>
			</tr>
			<tr>
				<td colspan=3>
					<?php echo $form->feed_relationship->label(__('Relationship to Item'))->renderRow(); ?>
				</td>
			</tr>
			<tr>
				<td colspan=3>
					<?php echo $form->createdAt->renderRow(array('size' => 50, 'readonly'=>'true'), 'Created On')  ?>
				</td>
			</tr>
			<?php if ($resource->statusId != QubitTerm::PENDING_ID) { ?>
				<tr>
					<td colspan=3>
						<?php echo $form->completedAt->label(__('Completed At'))->renderRow(); ?>
					</td>
				</tr>
			<?php } ?>
			<?php if ($resource->statusId == QubitTerm::PENDING_ID) { ?>
				<tr>
					<td colspan=3>
						<p style="color:#424242"><input type="checkbox" name="cbCompleted" value="true" checked="true" /><?php echo __('Complete') ?>
					</td>
				</tr>
			<?php } ?>
		</fieldset> 
    </section>
	<section class="actions">
		<table width="100%" cellspacing=0 border="0" cellpadding="0" align="left" summary="">
		  <ul class="clearfix links">
			<li><?php echo link_to(__('Back to List'), array('module' => 'feedback', 'action' => 'browse'), array('title' => __('Back to list'), 'class' => 'c-btn')) ?></li>
			<?php if ($resource->statusId == QubitTerm::PENDING_ID) { ?>
				<li><input class="c-btn c-btn-submit" type="submit" id="feedback"  value="<?php echo __('Submit') ?>"/></li>
			<?php } ?>
		  </ul>
		</table>
	</section>
</body>
<?php end_slot() ?>
