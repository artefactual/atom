<div 
  class="modal fade"
  id="acl-modal-container-<?php echo $entityType; ?>"
  data-trigger-button="acl-add-<?php echo $entityType; ?>"
  data-bs-backdrop="static"
  tabindex="-1"
  aria-labelledby="acl-modal-heading-<?php echo $entityType; ?>"
  aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="acl-modal-heading-<?php echo $entityType; ?>">
          <?php echo __('Add %1%', ['%1%' => lcfirst($label)]); ?>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal">
          <span class="visually-hidden"><?php echo __('Close'); ?></span>
        </button>
      </div>
      <div class="modal-body">
        <form>
          <div>
            <label for="acl-autocomplete-<?php echo $entityType; ?>" class="form-label">
              <?php echo __('%1% name', ['%1%' => $label]); ?>
            </label>
            <select
              name="acl-autocomplete-<?php echo $entityType; ?>"
              id="acl-autocomplete-<?php echo $entityType; ?>"
              class="form-control form-autocomplete mb-1">
            </select>
            <input class="list" type="hidden" value="<?php echo url_for([
                'module' => $entityType,
                'action' => 'autocomplete',
                'showOnlyActors' => 'actor' == $entityType,
            ]); ?>"/>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <?php echo __('Cancel'); ?>
        </button>
        <button type="button" class="btn btn-success">
          <?php echo __('Submit'); ?>
        </button>
      </div>
    </div>
  </div>
</div>

<div class="table-responsive mb-3 acl-table-container d-none">
  <table class="table table-bordered mb-0 caption-top">
    <caption class="pt-0">
      <span class="d-inline-block"></span>
    </caption>
    <thead class="table-light">
      <tr>
        <th scope="col"><?php echo __('Action'); ?></th>
        <th scope="col"><?php echo __('Permission'); ?></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($basicActions as $key => $item) { ?>
        <tr>
          <td><?php echo __($item); ?></td>
          <td>
            <div class="form-check form-check-inline">
              <input
                class="form-check-input"
                type="radio"
                name="acl[<?php echo $key; ?>_{objectId}]"
                id="acl_grant_[<?php echo $key; ?>_{objectId}]"
                value="<?php echo QubitAcl::GRANT; ?>">
              <label class="form-check-label" for="acl_grant_[<?php echo $key; ?>_{objectId}]">
                <?php echo __('Grant'); ?>
              </label>
            </div>
            <div class="form-check form-check-inline">
              <input
                class="form-check-input"
                type="radio"
                name="acl[<?php echo $key; ?>_{objectId}]"
                id="acl_deny_[<?php echo $key; ?>_{objectId}]"
                value="<?php echo QubitAcl::DENY; ?>">
              <label class="form-check-label" for="acl_deny_[<?php echo $key; ?>_{objectId}]">
                <?php echo __('Deny'); ?>
              </label>
            </div>
            <div class="form-check form-check-inline">
              <input
                class="form-check-input"
                type="radio"
                checked
                name="acl[<?php echo $key; ?>_{objectId}]"
                id="acl_inherit_[<?php echo $key; ?>_{objectId}]"
                value="<?php echo QubitAcl::INHERIT; ?>">
              <label class="form-check-label" for="acl_inherit_[<?php echo $key; ?>_{objectId}]">
                <?php echo __('Inherit'); ?>
              </label>
            </div>
          </td>
        </tr>
      <?php } ?>
    </tbody>
  </table>
</div>
