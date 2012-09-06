<h1><?php echo __('Move %1%', array('%1%' => render_title($resource))) ?></h1>

<div class="search">
  <form action="<?php echo url_for(array($resource, 'module' => 'default', 'action' => 'move')) ?>">
    <input name="query" value="<?php echo $sf_request->query ?>"/>
    <input class="form-submit" type="submit" value="<?php echo __('Search') ?>"/>
  </form>
</div>

<div id="breadcrumb">

  <h2 class="element-invisible"><?php echo __('You are here') ?></h2>

  <div class="content">
    <ol>

      <?php foreach ($parent->ancestors as $item): ?>
        <?php if (isset($item->parent)): ?>
          <li><?php echo link_to(render_title($item), array($resource, 'module' => 'default', 'action' => 'move', 'parent' => $item->slug)) ?></li>
        <?php endif; ?>
      <?php endforeach; ?>

      <?php if (isset($parent->parent)): ?>
        <li><?php echo render_title($parent) ?></li>
      <?php endif; ?>

    </ol>
  </div>

</div>

<table class="sticky-enabled">
  <thead>
    <tr>
      <th>
        <?php echo __('Title') ?>
      </th>
    </tr>
  </thead><tbody>
    <?php foreach ($results as $item): ?>
      <tr class="<?php echo 0 == @++$row % 2 ? 'even' : 'odd' ?>">
        <td>
          <?php echo link_to_if($resource->lft > $item->lft || $resource->rgt < $item->rgt, render_title($item), array($resource, 'module' => 'default', 'action' => 'move', 'parent' => $item->slug)) ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php echo get_partial('default/pager', array('pager' => $pager)) ?>

<?php echo $form->renderFormTag(url_for(array($resource, 'module' => 'default', 'action' => 'move'))) ?>

  <?php echo $form->renderHiddenFields() ?>

  <div class="actions section">

    <h2 class="element-invisible"><?php echo __('Actions') ?></h2>

    <div class="content">
      <ul class="clearfix links">
        <li><input class="form-submit" type="submit" value="<?php echo __('Move here') ?>"/></li>
        <li><?php echo link_to(__('Cancel'), array($resource, 'module' => 'informationobject')) ?></li>
      </ul>
    </div>

  </div>

</form>
