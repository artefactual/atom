<?php echo $form->renderGlobalErrors(); ?>

<?php echo $form->renderFormTag(url_for([$resource, 'module' => $sf_context->getModuleName(), 'action' => 'editTermAcl']), ['id' => 'editForm']); ?>

  <?php echo $form->renderHiddenFields(); ?>

  <div class="accordion" id="term-acl">
    <div class="accordion-item">
      <h2 class="accordion-header" id="all-heading">
        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#all-collapse" aria-expanded="true" aria-controls="all-collapse">
          <?php echo __('Permissions for all %1%', ['%1%' => lcfirst(sfConfig::get('app_ui_label_term'))]); ?>
        </button>
      </h2>
      <div id="all-collapse" class="accordion-collapse collapse show" aria-labelledby="all-heading" data-bs-parent="#term-acl">
        <div class="accordion-body">
          <div class="form-item">

            <table id="allTerms" class="table table-bordered">
              <caption><em><?php echo __('All %1%', ['%1%' => lcfirst(sfConfig::get('app_ui_label_term'))]); ?></em></caption>
              <thead>
                <tr>
                  <th scope="col"><?php echo __('Action'); ?></th>
                  <th scope="col"><?php echo __('Permission'); ?></th>
                </tr>
              </thead><tbody>
                <?php foreach ($termActions as $key => $item) { ?>
                  <tr class="<?php echo (0 == @++$row % 2) ? 'even' : 'odd'; ?>">
                    <td><?php echo __($item); ?></td>
                    <td>
                      <ul class="radio inline">
                        <?php if (isset($rootPermissions[$key])) { ?>
                          <li><input type="radio" name="acl[<?php echo $rootPermissions[$key]->id; ?>]" value="<?php echo QubitAcl::GRANT; ?>"<?php echo (1 == $rootPermissions[$key]->grantDeny) ? ' checked="checked"' : ''; ?>><?php echo __('Grant'); ?></li>
                          <li><input type="radio" name="acl[<?php echo $rootPermissions[$key]->id; ?>]" value="<?php echo QubitAcl::DENY; ?>"<?php echo (0 == $rootPermissions[$key]->grantDeny) ? ' checked="checked"' : ''; ?>><?php echo __('Deny'); ?></li>
                          <li><input type="radio" name="acl[<?php echo $rootPermissions[$key]->id; ?>]" value="<?php echo QubitAcl::INHERIT; ?>"><?php echo __('Inherit'); ?></li>
                        <?php } else { ?>
                          <?php $rootTermUrl = url_for([QubitTerm::getById(QubitTerm::ROOT_ID), 'module' => 'term']); ?>
                          <li><input type="radio" name="acl[<?php echo $key; ?>_<?php echo $rootTermUrl; ?>]" value="<?php echo QubitAcl::GRANT; ?>"><?php echo __('Grant'); ?></li>
                          <li><input type="radio" name="acl[<?php echo $key; ?>_<?php echo $rootTermUrl; ?> ?>]" value="<?php echo QubitAcl::DENY; ?>"><?php echo __('Deny'); ?></li>
                          <li><input type="radio" name="acl[<?php echo $key; ?>_<?php echo $rootTermUrl; ?> ?>]" value="<?php echo QubitAcl::INHERIT; ?>" checked="checked"><?php echo __('Inherit'); ?></li>
                        <?php } ?>
                      </ul>
                    </td>
                  </tr>
                <?php } ?>
              </tbody>
            </table>

          </div>
        </div>
      </div>
    </div>
    <div class="accordion-item">
      <h2 class="accordion-header" id="taxonomy-heading">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#taxonomy-collapse" aria-expanded="false" aria-controls="taxonomy-collapse">
          <?php echo __('Permissions by taxonomy'); ?>
        </button>
      </h2>
      <div id="taxonomy-collapse" class="accordion-collapse collapse" aria-labelledby="taxonomy-heading" data-bs-parent="#term-acl">
        <div class="accordion-body">
          <?php if (0 < count($taxonomyPermissions)) { ?>

            <?php foreach ($taxonomyPermissions as $key => $item) { ?>

              <div class="form-item">
                <table id="acl_<?php echo $key; ?>" class="table table-bordered">
                  <caption><?php echo render_title(QubitTaxonomy::getBySlug($key)); ?></caption>
                  <thead>
                    <tr>
                      <th scope="col"><?php echo __('Action'); ?></th>
                      <th scope="col"><?php echo __('Permission'); ?></th>
                    </tr>
                  </thead><tbody>
                    <?php foreach ($termActions as $action => $label) { ?>
                      <tr>
                        <td><?php echo __($label); ?></td>
                        <td id="<?php echo 'repo_'.$key.'_'.$action; ?>">
                          <ul class="radio inline">
                            <?php if (isset($item[$action])) { ?>
                              <li><input type="radio" name="acl[<?php echo $item[$action]->id; ?>]" value="<?php echo QubitAcl::GRANT; ?>"<?php echo (1 == $item[$action]->grantDeny) ? ' checked="checked"' : ''; ?>><?php echo __('Grant'); ?></li>
                              <li><input type="radio" name="acl[<?php echo $item[$action]->id; ?>]" value="<?php echo QubitAcl::DENY; ?>"<?php echo (0 == $item[$action]->grantDeny) ? ' checked="checked"' : ''; ?>><?php echo __('Deny'); ?></li>
                              <li><input type="radio" name="acl[<?php echo $item[$action]->id; ?>]" value="<?php echo QubitAcl::INHERIT; ?>"><?php echo __('Inherit'); ?></li>
                            <?php } else { ?>
                              <?php $rootTermUrl = url_for([QubitTerm::getById(QubitTerm::ROOT_ID), 'module' => 'term']); ?>
                              <li><input type="radio" name="acl[<?php echo $action.'_'.$rootTermUrl; ?>]" value="<?php echo QubitAcl::GRANT; ?>"><?php echo __('Grant'); ?></li>
                              <li><input type="radio" name="acl[<?php echo $action.'_'.$rootTermUrl; ?>]" value="<?php echo QubitAcl::DENY; ?>"><?php echo __('Deny'); ?></li>
                              <li><input type="radio" name="acl[<?php echo $action.'_'.$rootTermUrl; ?>]" value="<?php echo QubitAcl::INHERIT; ?>" checked="checked"><?php echo __('Inherit'); ?></li>
                            <?php } ?>
                          </ul>
                        </td>
                      </tr>
                    <?php } ?>
                  </tbody>
                </table>
              </div>

            <?php } ?>

          <?php } ?>

          <!-- Add taxonomy div - cut by aclDialog.js
          <div class="form-item" id="addTaxonomy">
            <?php echo $form->taxonomy->
              label(__('Taxonomy name'))->
              renderRow(['class' => 'form-autocomplete']); ?>
          </div>
          -->

          <div class="form-item">
            <label for="addTaxonomyLink"><?php echo __('Add permissions by taxonomy'); ?></label>
            <a id="addTaxonomyLink" href="#"><?php echo __('Add taxonomy'); ?></a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <section class="actions">
    <ul>
      <li><?php echo link_to(__('Cancel'), [$resource, 'module' => $sf_context->getModuleName(), 'action' => 'indexTermAcl'], ['class' => 'c-btn']); ?></li>
      <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Save'); ?>"/></li>
    </ul>
  </section>

</form>
