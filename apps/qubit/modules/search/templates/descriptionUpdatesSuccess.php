<?php decorate_with('layout_2col') ?>

<?php slot('title') ?>
  <h1 class="multiline">
    <?php echo image_tag('/images/icons-large/icon-new.png', array('width' => '42', 'height' => '42', 'alt' => '')) ?>
    <?php echo __('Browse newest additions') ?>
  </h1>
<?php end_slot() ?>

<?php slot('sidebar') ?>

  <section class="sidebar-widget">

    <h2><?php echo __('Filter options') ?></h2>

    <div>

      <?php echo $form->renderFormTag(url_for(array('module' => 'search', 'action' => 'descriptionUpdates')), array('method' => 'get')) ?>

        <?php echo $form->renderHiddenFields() ?>

        <?php echo $form->className
          ->label('Type')
          ->renderRow() ?>

        <?php echo $form->dateOf->renderRow() ?>

        <?php echo $form->publicationStatus
          ->label(__('Publication status (%1% only)', array('%1%' => sfConfig::get('app_ui_label_informationobject'))))
          ->renderRow() ?>

        <button type="submit" class="btn"><?php echo __('Search') ?></button>

      </form>

    </div>

  </section>

<?php end_slot() ?>

<?php slot('content') ?>

  <table class="table table-bordered sticky-enabled" id="clipboardButtonNode">
    <thead>
      <tr>
        <th><?php echo __($nameColumnDisplay); ?></th>
        <?php if ('QubitInformationObject' == $className && 0 < sfConfig::get('app_multi_repository')): ?>
          <th><?php echo __('Repository') ?></th>
        <?php elseif ('QubitTerm' == $className): ?>
          <th><?php echo __('Taxonomy'); ?></th>
        <?php endif; ?>
        <?php if ('CREATED_AT' != $form->getValue('dateOf')): ?>
          <th style="width: 110px"><?php echo __('Updated'); ?></th>
        <?php else: ?>
          <th style="width: 110px"><?php echo __('Created'); ?></th>
        <?php endif; ?>
        <?php if ('QubitInformationObject' == $className || 'QubitActor' == $className || 'QubitRepository' == $className): ?>
          <th style="width: 110px">
            <a href="#" class="all">All</a>
            <div class="separator" style="display: inline;">/</div>
            <a href="#" class="none">None</a>
          </th>
        <?php endif; ?>
      </tr>
    </thead><tbody>
      <?php foreach ($pager->getResults() as $result): ?>

        <tr class="<?php echo 0 == @++$row % 2 ? 'even' : 'odd' ?>">

          <td>

            <?php if ('QubitInformationObject' == $className): ?>

              <?php echo link_to(render_title($result->getTitle(array('cultureFallback' => true))), array($result, 'module' => 'informationobject')) ?>
              <?php $status = $result->getPublicationStatus() ?>
              <?php if (isset($status) && $status->statusId == QubitTerm::PUBLICATION_STATUS_DRAFT_ID): ?><span class="note2"><?php echo ' ('.$status->status.')' ?></span><?php endif; ?>

            <?php elseif ('QubitActor' == $className): ?>

              <?php $name = render_title($result->getAuthorizedFormOfName(array('cultureFallback' => true))) ?>
              <?php echo link_to($name, array($result, 'module' => 'actor')) ?>

            <?php elseif ('QubitFunction' == $className): ?>

              <?php $name = render_title($result->getAuthorizedFormOfName(array('cultureFallback' => true))) ?>
              <?php echo link_to($name, array($result, 'module' => 'function')) ?>

            <?php elseif ('QubitRepository' == $className): ?>

              <?php $name = render_title($result->getAuthorizedFormOfName(array('cultureFallback' => true))) ?>
              <?php echo link_to($name, array($result, 'module' => 'repository')) ?>

            <?php elseif ('QubitTerm' == $className): ?>

              <?php $name = render_title($result->getName(array('cultureFallback' => true))) ?>
              <?php echo link_to($name, array($result, 'module' => 'term')) ?>

            <?php endif; ?>

          </td>

          <?php if ('QubitInformationObject' == $className && 0 < sfConfig::get('app_multi_repository')): ?>
            <td>
              <?php if (null !== $repository = $result->getRepository(array('inherit' => true))): ?>
                <?php echo $repository->getAuthorizedFormOfName(array('cultureFallback' => true)) ?>
              <?php endif; ?>
            </td>
          <?php elseif('QubitTerm' == $className): ?>
            <td><?php echo $result->taxonomy->getName(array('cultureFallback' => true)) ?></td>
          <?php endif; ?>

          <td>
            <?php if ('CREATED_AT' != $form->getValue('dateOf')): ?>
              <?php echo $result->updatedAt ?>
            <?php else: ?>
              <?php echo $result->createdAt ?>
            <?php endif; ?>
          </td>

          <td>
            <?php if ('QubitInformationObject' == $className || 'QubitActor' == $className || 'QubitRepository' == $className): ?>
              <?php echo get_component('informationobject', 'clipboardButton', array('slug' => $result->slug, 'wide' => true)) ?>
            <?php endif; ?>
          </td>

        </tr>

      <?php endforeach; ?>
    </tbody>
  </table>

<?php end_slot() ?>

<?php slot('after-content') ?>
  <?php echo get_partial('default/pager', array('pager' => $pager)) ?>
<?php end_slot() ?>
