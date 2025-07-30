<?php decorate_with('layout_1col.php') ?>

<?php slot('title') ?>
	<h1 class="multiline"> <!--changed from 'label'-->
		<?php echo render_title(__('Feedback')) ?>
		<br>
		<br>
		<?php echo render_title($feedback) ?>
	</h1>
<?php end_slot() ?>

<?php 
$config['base_url'] = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http");
$config['base_url'] .= "://".$_SERVER['HTTP_HOST'];
$config['base_url'] .= str_replace(basename($_SERVER['SCRIPT_NAME']),"",$_SERVER['SCRIPT_NAME']);
?>

<?php slot('content') ?>
	<body>
	<?php echo $form->renderGlobalErrors() ?>
	<?php echo $form->renderFormTag(url_for(array($feedback, 'module' => 'feedback', 'action' => 'editFeedbackGeneral'))) ?>
	<?php echo $form->renderHiddenFields() ?>
    <section id="content">
		<fieldset class="collapsible">
		<legend><?php echo __('Feedback area') ?></legend>
			<tr>
				<td colspan=3>
					<?php echo $form->feed_type->label(__('Feedback Type'))->renderRow(); ?>
				</td>
			</tr>
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
					<?php echo $form->feed_relationship->label(__('Relationship to item'))->renderRow(); ?>
				</td>
			</tr>
		</fieldset> 
    </section>
	<section class="actions">
		<table width="100%" cellspacing=0 border="0" cellpadding="0" align="left" summary="">
		  <ul class="clearfix links">
			<li><a href="<?php echo $config['base_url'] ?>" class="c-btn">Cancel</a></li>
			<li><input class="c-btn c-btn-submit" type="submit" id="feedback"  value="<?php echo __('Submit') ?>"/></li>
		  </ul>
		</table>
	</section>
</body>
<?php end_slot() ?>
