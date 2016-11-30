<?php decorate_with('layout_1col.php') ?>

<?php slot('title') ?>
  <h1><?php echo __('Are you sure you want to clear the %1 clipboard ?', array('%1' => lcfirst($typeLabel))) ?></h1>
<?php end_slot() ?>

<?php slot('content') ?>

  <?php echo $form->renderFormTag(url_for(array('module' => 'user', 'action' => 'clipboardClear', 'type' => $type)),
                                  array('method' => 'delete')) ?>

    <?php echo $form->renderHiddenFields() ?>

    <div id="content">

      <?php if (1 == count($resultSet)): ?>
        <h2><?php echo __('There is one item added:') ?></h2>
      <?php else: ?>
        <h2><?php echo __('There are %1% items added:', array('%1%' => count($resultSet))) ?></h2>
      <?php endif; ?>

      <div class="delete-list">
        <ul>
          <?php foreach ($resultSet as $hit): ?>
            <?php $doc = $hit->getData() ?>
            <?php if ('QubitInformationObject' === $type): ?>
              <li><?php echo link_to(render_title(get_search_i18n($doc, 'title', array('allowEmpty' => false))), array('module' => 'informationobject', 'slug' => $doc['slug'])) ?></li>
            <?php elseif ('QubitActor' === $type): ?>
              <li><?php echo link_to(render_title(get_search_i18n($doc, 'authorizedFormOfName', array('allowEmpty' => false))), array('module' => 'actor', 'slug' => $doc['slug'])) ?></li>
            <?php elseif ('QubitRepository' === $type): ?>
              <li><?php echo link_to(render_title(get_search_i18n($doc, 'authorizedFormOfName', array('allowEmpty' => false))), array('module' => 'repository', 'slug' => $doc['slug'])) ?></li>
            <?php endif; ?>
          <?php endforeach; ?>
        </ul>
      </div>

    </div>

    <section class="actions">
      <ul>
        <li><?php echo link_to(__('Cancel'), array('module' => 'user', 'action' => 'clipboard'), array('class' => 'c-btn')) ?></li>
        <li><input class="c-btn c-btn-delete" type="submit" value="<?php echo __('Clear') ?>"/></li>
      </ul>
    </section>

  </form>

<?php end_slot() ?>
