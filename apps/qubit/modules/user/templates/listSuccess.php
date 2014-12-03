<h1><?php echo __('List users') ?></h1>

<section class="header-options">
  <div class="row">
    <div class="span6">
      <?php echo get_component('search', 'inlineSearch', array(
        'label' => __('Search users'),
        'route' => url_for(array('module' => 'user', 'action' => 'list')))) ?>
    </div>
  </div>
</section>

<ul class="nav nav-pills">
  <li<?php if ('onlyInactive' != $sf_request->filter): ?> class="active"<?php endif; ?>><?php echo link_to(__('Show active only'), array('filter' => 'onlyActive') + $sf_data->getRaw('sf_request')->getParameterHolder()->getAll()) ?></li>
  <li<?php if ('onlyInactive' == $sf_request->filter): ?> class="active"<?php endif; ?>><?php echo link_to(__('Show inactive only'), array('filter' => 'onlyInactive') + $sf_data->getRaw('sf_request')->getParameterHolder()->getAll()) ?></li>
</ul>

<table class="table table-bordered sticky-enabled">
  <thead>
    <tr>
      <th>
        <?php echo __('User name') ?>
      </th><th>
        <?php echo __('Email') ?>
      </th><th>
        <?php echo __('User groups') ?>
      </th>
    </tr>
  </thead><tbody>
    <?php foreach ($users as $item): ?>
      <tr>
        <td>
          <?php echo link_to($item->username, array($item, 'module' => 'user')) ?>
          <?php if (!$item->active): ?>
            (<?php echo __('inactive') ?>)
          <?php endif; ?>
          <?php if ($sf_user->user === $item): ?>
            (<?php echo __('you') ?>)
          <?php endif; ?>
        </td><td>
          <?php echo $item->email ?>
        </td><td>
          <ul>
            <?php foreach ($item->getAclGroups() as $group): ?>
              <li><?php echo render_title($group) ?></li>
            <?php endforeach; ?>
          </ul>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php echo get_partial('default/pager', array('pager' => $pager)) ?>

<section class="actions">
  <ul>
    <li><?php echo link_to(__('Add new'), array('module' => 'user', 'action' => 'add'), array('class' => 'c-btn')) ?></li>
  </ul>
</div>
