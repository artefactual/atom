<?php decorate_with('layout_1col') ?>

<?php slot('title') ?>
  <h1>
    <?php if (isset($icon)): ?>
      <?php echo image_tag('/images/icons-large/icon-'.$icon.'.png') ?>
    <?php endif; ?>
    <?php echo __('Browse %1% %2%', array(
      '%1%' => $pager->getNbResults(),
      '%2%' => strtolower(render_title($resource)))) ?>
  </h1>
<?php end_slot() ?>

<?php slot('before-content') ?>

  <section class="header-options">

    <?php echo get_partial('default/sortPicker',
      array(
        'options' => array(
          'alphabetic' => __('Alphabetic'),
          'relevancy' => __('Relevancy')))) ?>

  </section>

<?php end_slot() ?>

<?php slot('content') ?>

  <table class="table table-bordered sticky-enabled">
    <thead>
      <tr>
        <th>
          <?php echo render_title($resource) ?>
        </th><th>
          <?php echo __('Results') ?>
        </th>
      </tr>
    </thead><tbody>
      <?php foreach ($terms as $item): ?>
        <tr>
          <td>
            <?php echo link_to(render_title($item), array($item, 'module' => 'term', 'action' => 'browseTerm')) ?>
          </td><td>
            <?php echo $item->countRelatedInformationObjects() ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

<?php end_slot() ?>

<?php slot('after-content') ?>
  <?php echo get_partial('default/pager', array('pager' => $pager)) ?>
<?php end_slot() ?>
