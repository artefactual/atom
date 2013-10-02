<?php use_helper('Javascript') ?>

<?php $sf_response->addJavaScript('/vendor/yui/container/container-min') ?>
<?php $sf_response->addJavaScript('aclDialog') ?>

<?php echo $form->renderFormTag(url_for(array($resource, 'module' => $sf_context->getModuleName(), 'action' => 'editTermAcl')), array('id' => 'editForm')) ?>

  <section id="content">

    <fieldset class="collapsible" id="allTermsArea">

      <legend><?php echo __('Permissions for all %1%', array('%1%' => lcfirst(sfConfig::get('app_ui_label_term')))) ?></legend>

      <div class="form-item">

        <table id="allTerms" class="table table-bordered">
          <caption><em><?php echo __('All %1%', array('%1%' => lcfirst(sfConfig::get('app_ui_label_term')))) ?></em></caption>
          <thead>
            <tr>
              <th scope="col"><?php echo __('Action') ?></th>
              <th scope="col"><?php echo __('Permission') ?></th>
            </tr>
          </thead><tbody>
            <?php foreach ($termActions as $key => $item): ?>
              <tr class="<?php echo (0 == @++$row % 2) ? 'even' : 'odd' ?>">
                <td><?php echo __($item) ?></td>
                <td>
                  <ul class="radio inline">
                    <?php if (isset($rootPermissions[$key])): ?>
                      <li><input type="radio" name="acl[<?php echo $rootPermissions[$key]->id ?>]" value="<?php echo QubitAcl::GRANT ?>"<?php echo (1 == $rootPermissions[$key]->grantDeny) ? ' checked="checked"' : '' ?>><?php echo __('Grant') ?></li>
                      <li><input type="radio" name="acl[<?php echo $rootPermissions[$key]->id ?>]" value="<?php echo QubitAcl::DENY ?>"<?php echo (0 == $rootPermissions[$key]->grantDeny) ? ' checked="checked"' : '' ?>><?php echo __('Deny') ?></li>
                      <li><input type="radio" name="acl[<?php echo $rootPermissions[$key]->id ?>]" value="<?php echo QubitAcl::INHERIT?>"><?php echo __('Inherit') ?></li>
                    <?php else: ?>
                      <?php $rootTermUrl = url_for(array(QubitTerm::getById(QubitTerm::ROOT_ID), 'module' => 'term')) ?>
                      <li><input type="radio" name="acl[<?php echo $key ?>_<?php echo $rootTermUrl ?>]" value="<?php echo QubitAcl::GRANT ?>"><?php echo __('Grant') ?></li>
                      <li><input type="radio" name="acl[<?php echo $key ?>_<?php echo $rootTermUrl ?> ?>]" value="<?php echo QubitAcl::DENY ?>"><?php echo __('Deny') ?></li>
                      <li><input type="radio" name="acl[<?php echo $key ?>_<?php echo $rootTermUrl ?> ?>]" value="<?php echo QubitAcl::INHERIT ?>" checked="checked"><?php echo __('Inherit') ?></li>
                    <?php endif; ?>
                  </ul>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

      </div>

    </fieldset> <!-- /#allTermsArea -->

    <fieldset class="collapsible collapsed" id="taxonomyArea">

      <legend><?php echo __('Permissions by taxonomy') ?></legend>

      <?php if (0 < count($taxonomyPermissions)): ?>

        <?php foreach ($taxonomyPermissions as $key => $item): ?>

          <div class="form-item">
            <table id="acl_<?php echo $key ?>" class="table table-bordered">
              <caption><?php echo render_title(QubitTaxonomy::getBySlug($key)) ?></caption>
              <thead>
                <tr>
                  <th scope="col"><?php echo __('Action') ?></th>
                  <th scope="col"><?php echo __('Permission') ?></th>
                </tr>
              </thead><tbody>
                <?php foreach ($termActions as $action => $label): ?>
                  <tr>
                    <td><?php echo __($label) ?></td>
                    <td id="<?php echo 'repo_'.$key.'_'.$action ?>">
                      <ul class="radio inline">
                        <?php if (isset($item[$action])): ?>
                          <li><input type="radio" name="acl[<?php echo $item[$action]->id ?>]" value="<?php echo QubitAcl::GRANT ?>"<?php echo (1 == $item[$action]->grantDeny) ? ' checked="checked"' : '' ?>><?php echo __('Grant') ?></li>
                          <li><input type="radio" name="acl[<?php echo $item[$action]->id ?>]" value="<?php echo QubitAcl::DENY ?>"<?php echo (0 == $item[$action]->grantDeny) ? ' checked="checked"' : '' ?>><?php echo __('Deny') ?></li>
                          <li><input type="radio" name="acl[<?php echo $item[$action]->id ?>]" value="<?php echo QubitAcl::INHERIT?>"><?php echo __('Inherit') ?></li>
                        <?php else: ?>
                          <?php $rootTermUrl = url_for(array(QubitTerm::getById(QubitTerm::ROOT_ID), 'module' => 'term')) ?>
                          <li><input type="radio" name="acl[<?php echo $action.'_'.$rootTermUrl ?>]" value="<?php echo QubitAcl::GRANT ?>"><?php echo __('Grant') ?></li>
                          <li><input type="radio" name="acl[<?php echo $action.'_'.$rootTermUrl ?>]" value="<?php echo QubitAcl::DENY ?>"><?php echo __('Deny') ?></li>
                          <li><input type="radio" name="acl[<?php echo $action.'_'.$rootTermUrl ?>]" value="<?php echo QubitAcl::INHERIT ?>" checked="checked"><?php echo __('Inherit') ?></li>
                        <?php endif; ?>
                      </ul>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

        <?php endforeach; ?>

      <?php endif; ?>


    <?php

      // Build dialog for adding new table
      $tableTemplate  = '<div class="form-item">';
      $tableTemplate .= '<table id="acl_{objectId}" class="table table-bordered">';
      $tableTemplate .= '<caption/>';
      $tableTemplate .= '<thead>';
      $tableTemplate .= '<tr>';
      $tableTemplate .= '<th scope="col">'.__('Action').'</th>';
      $tableTemplate .= '<th scope="col">'.__('Permissions').'</th>';
      $tableTemplate .= '</tr>';
      $tableTemplate .= '</thead>';
      $tableTemplate .= '<tbody>';

      foreach ($termActions as $key => $item)
      {
        $tableTemplate .= '<tr>';
        $tableTemplate .= '<td>'.__($item).'</th>';
        $tableTemplate .= '<td><ul class="radio inline">';
        $tableTemplate .= '<li><input type="radio" name="acl['.$key.'_{objectId}]" value="'.QubitAcl::GRANT.'"/>'.__('Grant').'</li>';
        $tableTemplate .= '<li><input type="radio" name="acl['.$key.'_{objectId}]" value="'.QubitAcl::DENY.'"/>'.__('Deny').'</li>';
        $tableTemplate .= '<li><input type="radio" name="acl['.$key.'_{objectId}]" value="'.QubitAcl::INHERIT.'" checked/>'.__('Inherit').'</li>';
        $tableTemplate .= '</ul></td>';
        $tableTemplate .= "</tr>";
        $tableTemplate .= "</div>";
      }

      $tableTemplate .= '</tbody>';
      $tableTemplate .= '</table>';

echo javascript_tag(<<<EOL
Drupal.behaviors.dialog = {
  attach: function (context)
  {
    Qubit.taxonomyDialog = new QubitAclDialog('addTaxonomy', '$tableTemplate', jQuery);
  }
}
EOL
);

?>

      <!-- Add taxonomy div - cut by aclDialog.js -->
      <div class="form-item" id="addTaxonomy">
        <?php echo $form->taxonomy->
          label(__('Taxonomy name'))->
          renderRow(array('class' => 'form-autocomplete')) ?>
      </div>

      <div class="form-item">
        <label for="addTaxonomyLink"><?php echo __('Add permissions by taxonomy') ?></label>
        <a id="addTaxonomyLink" href="javascript:Qubit.taxonomyDialog.show()"><?php echo __('Add taxonomy') ?></a>
      </div>

  </fieldset> <!-- /#taxonomyArea -->

  </section>

  <section class="actions">
    <ul>
      <li><?php echo link_to(__('Cancel'), array($resource, 'module' => $sf_context->getModuleName(), 'action' => 'indexTermAcl'), array('class' => 'c-btn')) ?></li>
      <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Save') ?>"/></li>
    </ul>
  </section>

</form>
