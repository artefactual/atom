<h1><?php echo __('List %1%', array('%1%' => sfConfig::get('app_ui_label_repository'))) ?></h1>

<?php if (isset($error)): ?>

  <div class="search-results">
    <ul>
      <li><?php echo $error ?></li>
    </ul>
  </div>

<?php else: ?>

  <table class="sticky-enabled">
    <thead>
      <tr>
        <th>
          <?php echo __('Name') ?>
        </th><th>
          <?php echo __('Type') ?>
        </th><th>
          <?php echo __('Country') ?>
        </th>
      </tr>
    </thead><tbody>
      <?php foreach ($repositories as $item): ?>
        <tr class="<?php echo 0 == @++$row % 2 ? 'even' : 'odd' ?>">
          <td>
            <?php echo link_to(render_title($item), array($item, 'module' => 'repository')) ?>
          </td><td>
            <ul>
              <?php foreach ($item->getTermRelations(QubitTaxonomy::REPOSITORY_TYPE_ID) as $relation): ?>
                <li><?php echo $relation->term ?></li>
              <?php endforeach; ?>
            </ul>
          </td><td>
            <?php echo $item->getCountry() ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <?php echo get_partial('default/pager', array('pager' => $pager)) ?>

<?php endif; ?>

<div class="search">
  <form action="<?php echo url_for(array('module' => 'repository', 'action' => 'list')) ?>">
    <input name="subquery" value="<?php echo esc_entities($sf_request->subquery) ?>"/>
    <input class="form-submit" type="submit" value="<?php echo __('Search %1%', array('%1%' => sfConfig::get('app_ui_label_repository'))) ?>"/>
  </form>
</div>
