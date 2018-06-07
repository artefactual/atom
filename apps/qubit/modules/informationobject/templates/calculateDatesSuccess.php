<?php decorate_with('layout_2col') ?>

<?php slot('sidebar') ?>
  <?php include_component('informationobject', 'contextMenu') ?>
<?php end_slot() ?>

<?php slot('title') ?>

  <h1><?php echo __('Calculate dates') ?></h1>

<?php end_slot() ?>

<?php slot('content') ?>

  <?php echo $form->renderFormTag(url_for(array($resource, 'module' => 'informationobject', 'action' => 'calculateDates'))) ?>

    <div id="content">

      <fieldset class="collapsible">
        <div class="fieldset-wrapper">

          <?php echo $form->eventId->renderRow() ?>

          <h6>
            <?php echo __('Note: While the date range update is running, the selected description should not be edited.') ?>
            <?php echo __('You can check %1% page to determine the current status of the update job.',
              array('%1%' => link_to(__('Manage jobs'), array('module' => 'jobs', 'action' => 'browse')))) ?>
          </h6>

        </div>
      </fieldset>

    </div>

    <section class="actions">
      <ul>
        <li><?php echo link_to(__('Cancel'), array($resource, 'module' => 'informationobject'), array('class' => 'c-btn')) ?></li>
      </ul>
      <ul>
        <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Continue') ?>"/></li>
      </ul>
    </section>

  </form>

<?php end_slot() ?>
