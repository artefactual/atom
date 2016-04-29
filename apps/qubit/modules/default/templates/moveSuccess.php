<?php decorate_with('layout_1col') ?>

<?php slot('title') ?>
  <h1><?php echo __('Move %1%', array('%1%' => render_title($resource))) ?></h1>
<?php end_slot() ?>

<?php slot('before-content') ?>

  <div class="row">
    <div class="inline-search span6">
      <form action="<?php echo url_for(array($resource, 'module' => 'default', 'action' => 'move')) ?>">
        <div class="input-append">
          <?php if (isset($sf_request->query)): ?>
            <input type="text" name="query" value="<?php echo esc_entities($sf_request->query) ?>" />
            <a class="btn" href="<?php echo url_for(array($resource, 'module' => 'default', 'action' => 'move')) ?>">
              <i class="fa fa-times"></i>
            </a>
          <?php else: ?>
            <input type="text" name="query" placeholder="<?php echo __('Search title or identifier') ?>" />
          <?php endif; ?>
          <div class="btn-group">
            <button class="btn" type="submit">
              <i class="fa fa-search"></i>
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <section class="breadcrumb">
    <ul>
      <?php foreach ($parent->ancestors as $item): ?>
        <?php if (isset($item->parent)): ?>
          <li><?php echo link_to(render_title($item), array($resource, 'module' => 'default', 'action' => 'move', 'parent' => $item->slug)) ?></li>
        <?php endif; ?>
      <?php endforeach; ?>

      <?php if (isset($parent->parent)): ?>
        <li class="active"><span><?php echo render_title($parent) ?></span></li>
      <?php endif; ?>
    </ul>
  </section>

<?php end_slot() ?>

<?php slot('content') ?>

  <?php if (count($results)): ?>
    <table class="table table-bordered sticky-enabled">
      <thead>
        <tr>
          <th><?php echo __('Identifier') ?></th>
          <th><?php echo __('Title') ?></th>
        </tr>
      </thead><tbody>
        <?php foreach ($results as $item): ?>
          <tr>
            <td width="15%">
              <?php echo $item->identifier ?>
            </td>
            <td width="85%">
              <?php echo link_to_if($resource->lft > $item->lft || $resource->rgt < $item->rgt, render_title($item), array($resource, 'module' => 'default', 'action' => 'move', 'parent' => $item->slug)) ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>

<?php end_slot() ?>

<?php slot('after-content') ?>
  <?php echo get_partial('default/pager', array('pager' => $pager)) ?>

  <?php echo $form->renderFormTag(url_for(array($resource, 'module' => 'default', 'action' => 'move'))) ?>

    <?php echo $form->renderHiddenFields() ?>

    <section class="actions">
      <ul>
        <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Move here') ?>"/></li>
        <li><?php echo link_to(__('Cancel'), array($resource, 'module' => 'informationobject'), array('class' => 'c-btn')) ?></li>
      </ul>
    </section>

  </form>
<?php end_slot() ?>
