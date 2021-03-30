<?php decorate_with('layout_2col'); ?>

<?php slot('sidebar'); ?>
  <?php include_component('informationobject', 'contextMenu'); ?>
<?php end_slot(); ?>

<?php slot('title'); ?>

  <h1><?php echo __('Calculate dates'); ?></h1>

<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php echo $form->renderGlobalErrors(); ?>

  <?php echo $form->renderFormTag(url_for([$resource, 'module' => 'informationobject', 'action' => 'calculateDates'])); ?>

    <?php echo $form->renderHiddenFields(); ?>
    
    <div id="content">

      <fieldset class="collapsible">

        <?php if (count($events)) { ?>
          <legend class="collapse-processed"><?php echo __('Update an existing date range'); ?></legend>

          <div class="fieldset-wrapper">

            <p><?php echo __('Select a date range to overwrite:'); ?></p>

            <?php foreach ($events as $eventId => $eventName) { ?>
              <p><input type="radio" name="eventIdOrTypeId" value="<?php echo $eventId; ?>"><?php echo $eventName; ?></p>
            <?php } ?>

            <div class="alert alert-notice">
              <?php echo __('Updating an existing date range will permanently overwrite the current dates.'); ?>
            </div>

          </div>
        <?php } ?>

        <?php if (count($descendantEventTypes)) { ?>
          <legend class="collapse-processed"><?php echo __('or, create a new date range'); ?></legend>

          <div class="fieldset-wrapper">

            <p><?php echo __('Select the new date type:'); ?></p>

            <?php foreach ($descendantEventTypes as $eventTypeId => $eventTypeName) { ?>
              <p><input type="radio" name="eventIdOrTypeId" value="<?php echo $eventTypeId; ?>"><?php echo $eventTypeName; ?></p>
            <?php } ?>

          </div>
        <?php } ?>

      </fieldset>

    </div>
          <div class="alert alert-info">
            <?php echo __('Note: While the date range update is running, the selected description should not be edited.'); ?>
            <?php echo __('You can check %1% page to determine the current status of the update job.',
              ['%1%' => link_to(__('Manage jobs'), ['module' => 'jobs', 'action' => 'browse'])]); ?>
          </div>


    <section class="actions">
      <ul>
        <li><?php echo link_to(__('Cancel'), [$resource, 'module' => 'informationobject'], ['class' => 'c-btn']); ?></li>
      </ul>
      <ul>
        <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Continue'); ?>"/></li>
      </ul>
    </section>

  </form>

<?php end_slot(); ?>
