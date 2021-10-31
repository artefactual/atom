<?php decorate_with('layout_1col.php'); ?>

<?php slot('title'); ?>
  <div class="multiline-header d-flex flex-column mb-3">
    <h1 class="mb-0" aria-describedby="heading-label">
      <?php echo render_title($resource); ?>
    </h1>
    <span class="small" id="heading-label">
      <?php echo __(
          'Link %1%',
          ['%1%' => sfConfig::get('app_ui_label_physicalobject')]
      ); ?>
    </span>
  </div>
<?php end_slot(); ?>

<?php slot('content'); ?>
  <?php echo $form->renderGlobalErrors(); ?>
  <?php echo $form->renderFormTag(url_for([
      $resource,
      'module' => $sf_context->getModuleName(),
      'action' => 'editPhysicalObjects',
  ])); ?>
    <?php echo $form->renderHiddenFields(); ?>

    <?php if (0 < count($relations)) { ?>
      <div class="table-responsive mb-3">
        <table class="table table-bordered mb-0">
          <thead>
            <tr>
              <th style="width: 100%;">
                <?php echo __('Containers'); ?>
              </th>
              <th>
                <span class="visually-hidden">
                  <?php echo __('Actions'); ?>
                </span>
              </th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($relations as $item) { ?>
              <tr>
                <td>
                  <?php echo $item->subject->getLabel(); ?>
                </td>
                <td class="text-nowrap">
                  <a class="btn atom-btn-white me-1" href="<?php echo url_for(
                      [$item->subject, 'module' => 'physicalobject', 'action' => 'edit']
                  ); ?>">
                    <i class="fas fa-fw fa-pencil-alt" aria-hidden="true"></i>
                    <span class="visually-hidden"><?php echo __('Edit row'); ?></span>
                  </a>
                  <button
                    type="button"
                    class="btn atom-btn-white delete-physical-storage"
                    id="<?php echo url_for([$item, 'module' => 'relation']); ?>">
                    <i class="fas fa-fw fa-times" aria-hidden="true"></i>
                    <span class="visually-hidden"><?php echo __('Delete row'); ?></span>
                  </button>
                </td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    <?php } ?>

    <div class="accordion mb-3">
      <div class="accordion-item<?php echo count($relations) ? ' rounded-0' : ''; ?>">
        <h2 class="accordion-header" id="add-heading">
          <button
            class="accordion-button<?php echo count($relations) ? ' rounded-0' : ''; ?>"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#add-collapse"
            aria-expanded="true"
            aria-controls="add-collapse">
            <?php echo __('Add container links (duplicate links will be ignored)'); ?>
          </button>
        </h2>
        <div
          id="add-collapse"
          class="accordion-collapse collapse show"
          aria-labelledby="add-heading">
          <div class="accordion-body">
            <?php
                $extraInputs = '<input class="add" type="hidden" data-link-existing="false" value="'
                    .url_for([
                        $resource,
                        'module' => $sf_context->getModuleName(),
                        'action' => 'editPhysicalObjects',
                    ])
                    .' #name"><input class="list" type="hidden" value="'
                    .url_for([
                        'module' => 'physicalobject',
                        'action' => 'autocomplete',
                    ])
                    .'">';
                echo render_field(
                    $form->containers,
                    null,
                    ['class' => 'form-autocomplete', 'extraInputs' => $extraInputs]
                );
            ?>
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="create-heading">
          <button
            class="accordion-button collapsed"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#create-collapse"
            aria-expanded="false"
            aria-controls="create-collapse">
            <?php echo __('Or, create a new container'); ?>
          </button>
        </h2>
        <div
          id="create-collapse"
          class="accordion-collapse collapse"
          aria-labelledby="create-heading">
          <div class="accordion-body">
            <div class="form-item">
              <?php echo render_field($form->name); ?>
              <?php echo render_field($form->location); ?>
              <?php echo render_field($form->type); ?>
            </div>
          </div>
        </div>
      </div>
    </div>

    <ul class="actions mb-3 nav gap-2">
      <li>
        <?php echo link_to(
            __('Cancel'),
            [$resource, 'module' => $sf_context->getModuleName()],
            ['class' => 'btn atom-btn-outline-light', 'role' => 'button']
        ); ?>
      </li>
      <li>
        <input
          class="btn atom-btn-outline-success"
          type="submit"
          value="<?php echo __('Save'); ?>">
      </li>
    </ul>
  </form>
<?php end_slot(); ?>
