<h1>
  <?php echo __('Edit %1% permissions of %2%', [
      '%1%' => lcfirst(sfConfig::get('app_ui_label_actor')),
      '%2%' => render_title($resource),
  ]); ?>
</h1>

<?php echo get_partial('aclGroup/aclModal', [
    'entityType' => 'actor',
    'label' => sfConfig::get('app_ui_label_actor'),
    'basicActions' => $basicActions,
]); ?>

<?php echo $form->renderGlobalErrors(); ?>

<?php echo $form->renderFormTag(
    url_for([
        $resource,
        'module' => $sf_context->getModuleName(),
        'action' => 'editActorAcl',
    ]),
    ['id' => 'editForm']
); ?>

  <?php echo $form->renderHiddenFields(); ?>

  <div class="accordion mb-3">
    <div class="accordion-item">
      <h2 class="accordion-header" id="all-heading">
        <button
          class="accordion-button"
          type="button"
          data-bs-toggle="collapse"
          data-bs-target="#all-collapse"
          aria-expanded="true"
          aria-controls="all-collapse">
          <?php echo __(
              'Permissions for all %1%',
              ['%1%' => lcfirst(sfConfig::get('app_ui_label_actor'))]
          ); ?>
        </button>
      </h2>
      <div
        id="all-collapse"
        class="accordion-collapse collapse show"
        aria-labelledby="all-heading">
        <div class="accordion-body">
          <?php echo get_component('aclGroup', 'aclTable', [
              'object' => QubitActor::getById(QubitActor::ROOT_ID),
              'permissions' => $actors[QubitActor::ROOT_ID],
              'actions' => $basicActions,
          ]); ?>
        </div>
      </div>
    </div>
    <div class="accordion-item">
      <h2 class="accordion-header" id="actor-heading">
        <button
          class="accordion-button collapsed"
          type="button"
          data-bs-toggle="collapse"
          data-bs-target="#actor-collapse"
          aria-expanded="false"
          aria-controls="actor-collapse">
          <?php echo __(
              'Permissions by %1%',
              ['%1%' => lcfirst(sfConfig::get('app_ui_label_actor'))]
          ); ?>
        </button>
      </h2>
      <div
        id="actor-collapse"
        class="accordion-collapse collapse"
        aria-labelledby="actor-heading">
        <div class="accordion-body">
          <?php foreach ($actors as $key => $item) { ?>
            <?php if (QubitActor::ROOT_ID != $key) { ?>
              <?php echo get_component('aclGroup', 'aclTable', [
                  'object' => QubitActor::getById($key),
                  'permissions' => $item,
                  'actions' => $basicActions,
              ]); ?>
            <?php } ?>
          <?php } ?>

          <button
            class="btn atom-btn-white text-wrap"
            type="button"
            id="acl-add-actor"
            data-bs-toggle="modal"
            data-bs-target="#acl-modal-container-actor">
            <i class="fas fa-plus me-1" aria-hidden="true"></i>
            <?php echo __(
                'Add permissions by %1%',
                ['%1%' => lcfirst(sfConfig::get('app_ui_label_actor'))]
            ); ?>
          </button>
        </div>
      </div>
    </div>
  </div>

  <ul class="actions mb-3 nav gap-2">
    <li>
      <?php echo link_to(
          __('Cancel'),
          [$resource, 'module' => $sf_context->getModuleName(), 'action' => 'indexActorAcl'],
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
