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

    <div class="accordion mb-3">
      <?php if (count($events)) { ?>
        <div class="accordion-item">
          <h2 class="accordion-header" id="existing-heading">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#existing-collapse" aria-expanded="true" aria-controls="existing-collapse">
              <?php echo __('Update an existing date range'); ?>
            </button>
          </h2>
          <div id="existing-collapse" class="accordion-collapse collapse show" aria-labelledby="existing-heading">
            <div class="accordion-body">
              <div class="mb-3">
                <fieldset aria-describedby="calculate-dates-alert">
                  <legend class="fs-6">
                    <?php echo __('Select a date range to overwrite:'); ?>
                  </legend>
                  <?php foreach ($events as $eventId => $eventName) { ?>
                    <div class="form-check">
                      <input
                        type="radio"
                        name="eventIdOrTypeId"
                        class="form-check-input"
                        id="eventIdOrTypeId-<?php echo $eventId; ?>"
                        value="<?php echo $eventId; ?>">
                      <label for="eventIdOrTypeId-<?php echo $eventId; ?>" class="form-check-label">
                        <?php echo $eventName; ?>
                      </label>
                    </div>
                  <?php } ?>
                </fieldset>
              </div>
              <div class="alert alert-warning mb-0" id="calculate-dates-alert">
                <?php echo __('Updating an existing date range will permanently overwrite the current dates.'); ?>
              </div>
            </div>
          </div>
        </div>
      <?php } ?>
      <?php if (count($descendantEventTypes)) { ?>
        <div class="accordion-item">
          <h2 class="accordion-header" id="create-heading">
            <button class="accordion-button<?php echo count($events) ? ' collapsed' : ''; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#create-collapse" aria-expanded="<?php echo count($events) ? 'false' : 'true'; ?>" aria-controls="create-collapse">
              <?php echo __('Create a new date range'); ?>
            </button>
          </h2>
          <div id="create-collapse" class="accordion-collapse collapse<?php echo count($events) ? '' : ' show'; ?>" aria-labelledby="create-heading">
            <div class="accordion-body">
              <fieldset>
                <legend class="fs-6">
                  <?php echo __('Select the new date type:'); ?>
                </legend>
                <?php foreach ($descendantEventTypes as $eventTypeId => $eventTypeName) { ?>
                  <div class="form-check">
                    <input
                      type="radio"
                      name="eventIdOrTypeId"
                      class="form-check-input"
                      id="eventIdOrTypeId-<?php echo $eventTypeId; ?>"
                      value="<?php echo $eventTypeId; ?>">
                    <label for="eventIdOrTypeId-<?php echo $eventTypeId; ?>" class="form-check-label">
                      <?php echo $eventTypeName; ?>
                    </label>
                  </div>
                <?php } ?>
              </fieldset>
            </div>
          </div>
        </div>
      <?php } ?>
    </div>
    
    <div class="alert alert-info" role="alert">
      <?php echo __('Note: While the date range update is running, the selected description should not be edited.'); ?>
      <?php echo __('You can check %1% page to determine the current status of the update job.',
        ['%1%' => link_to(__('Manage jobs'), ['module' => 'jobs', 'action' => 'browse'], ['class' => 'alert-link'])]); ?>
    </div>

    <ul class="actions mb-3 nav gap-2">
      <li><?php echo link_to(__('Cancel'), [$resource, 'module' => 'informationobject'], ['class' => 'btn atom-btn-outline-light', 'role' => 'button']); ?></li>
      <li><input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Continue'); ?>"></li>
    </ul>

  </form>

<?php end_slot(); ?>
