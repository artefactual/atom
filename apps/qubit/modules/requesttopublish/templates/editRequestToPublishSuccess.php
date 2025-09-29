<?php decorate_with('layout_1col.php'); ?>

<?php slot('title'); ?>
	<h1 class="multiline"> <!--changed from 'label'-->
		<?php echo render_title(__('RequestToPublish')); ?>
		<br>
		<br>
		<?php echo render_title($resource); ?>
	</h1>
<?php end_slot(); ?>

<?php slot('content'); ?>
	<body>
	<?php echo $form->renderGlobalErrors(); ?>
	<?php echo $form->renderFormTag(url_for([$resource, 'module' => 'requesttopublish', 'action' => 'editRequestToPublish'])); ?>
	<?php echo $form->renderHiddenFields(); ?>
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
					<?php echo $form->rtp_need_image_by->label(__('Need image by'))->renderRow(); ?>
				</td>
			</tr>
			<tr>
				<td colspan=3>
					<?php echo $form->rtp_motivation->label(__('Motivation'))->renderRow(); ?>
				</td>
			</tr>
			<tr>
				<td colspan=3>
					<?php echo $form->createdAt->renderRow(['size' => 50, 'readonly' => 'true'], 'Created On'); ?>
				</td>
			</tr>
			<?php if (QubitTerm::IN_REVIEW_ID != $resource->statusId) { ?>
				<tr>
					<td colspan=3>
						<?php echo $form->completedAt->label(__('Completed At'))->renderRow(); ?>
					</td>
				</tr>
			<?php } ?>
			<?php if (QubitTerm::IN_REVIEW_ID == $resource->statusId && $resource->unique_identifier != $this->context->user->getAttribute('user_id')) { ?>
				<tr>
					<td colspan=3>
						<p style="color:#424242"><?php echo $form->outcome->renderRow(); ?>
					</td>
				</tr>
				
			<?php } ?>
		</fieldset> 
    </section>
	<section class="actions">
		<table width="100%" cellspacing=0 border="0" cellpadding="0" align="left" summary="">
		  <ul class="clearfix links">
			<li><?php echo link_to(__('Back to List'), ['module' => 'requesttopublish', 'action' => 'browse'], ['title' => __('Back to list'), 'class' => 'c-btn']); ?></li>
			<?php if (QubitTerm::IN_REVIEW_ID == $resource->statusId && $resource->unique_identifier != $this->context->user->getAttribute('user_id')) { ?>
				<li><input class="c-btn c-btn-submit" type="submit" id="requesttopublish"  value="<?php echo __('Submit'); ?>"/></li>
			<?php } ?>
		  </ul>
		</table>
	</section>
</body>
<?php end_slot(); ?>
