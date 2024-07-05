<h1><?php echo __('List users'); ?></h1>

<div class="d-inline-block mb-3">
  <?php echo get_component('search', 'inlineSearch', [
      'label' => __('Search users'),
      'landmarkLabel' => __('User'),
      'route' => url_for(['module' => 'user', 'action' => 'list']),
  ]); ?>
</div>

<nav>
  <ul class="nav nav-pills mb-3 d-flex gap-2">
    <?php $options = ['class' => 'btn atom-btn-white active-primary text-wrap']; ?>
    <?php if ('onlyInactive' != $sf_request->filter) { ?>
      <?php $options['class'] .= ' active'; ?>
      <?php $options['aria-current'] = 'page'; ?>
    <?php } ?>
    <li class="nav-item">
      <?php echo link_to(
          __('Show active only'),
          ['filter' => 'onlyActive']
          + $sf_data->getRaw('sf_request')->getParameterHolder()->getAll(),
          $options
      ); ?>
    </li>
    <?php $options = ['class' => 'btn atom-btn-white active-primary text-wrap']; ?>
    <?php if ('onlyInactive' == $sf_request->filter) { ?>
      <?php $options['class'] .= ' active'; ?>
      <?php $options['aria-current'] = 'page'; ?>
    <?php } ?>
    <li class="nav-item">
      <?php echo link_to(
          __('Show inactive only'),
          ['filter' => 'onlyInactive']
          + $sf_data->getRaw('sf_request')->getParameterHolder()->getAll(),
          $options
      ); ?>
    </li>
  </ul>
</nav>

<div class="table-responsive mb-3">
  <table class="table table-bordered mb-0">
    <thead>
      <tr>
        <th>
          <?php echo __('User name'); ?>
        </th><th>
          <?php echo __('Email'); ?>
        </th><th>
          <?php echo __('User groups'); ?>
        </th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($users as $item) { ?>
        <tr>
          <td>
            <?php echo link_to($item->username, [$item, 'module' => 'user']); ?>
            <?php if (!$item->active) { ?>
              (<?php echo __('inactive'); ?>)
            <?php } ?>
            <?php if ($sf_user->user === $item) { ?>
              (<?php echo __('you'); ?>)
            <?php } ?>
          </td><td>
            <?php echo $item->email; ?>
          </td><td>
            <ul>
              <?php foreach ($item->getAclGroups() as $group) { ?>
                <li><?php echo render_title($group); ?></li>
              <?php } ?>
            </ul>
          </td>
        </tr>
      <?php } ?>
    </tbody>
  </table>
</div>

<?php echo get_partial('default/pager', ['pager' => $pager]); ?>

<?php if (false === sfContext::getinstance()->user->getProviderConfigValue('auto_create_atom_user', true)) { ?>
  <section class="actions mb-3">
    <?php echo link_to(__('Add new'), ['module' => 'user', 'action' => 'add'], ['class' => 'btn atom-btn-outline-light']); ?>
  </section>
<?php } ?>
