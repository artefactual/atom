<?php decorate_with('layout_1col') ?>
<?php use_helper('Date') ?>
<?php use_helper('Text') ?>

<?php slot('title') ?>

  <div class="multiline-header">
    <?php echo image_tag('/images/icons-large/icon-new.png', array('width' => '42', 'height' => '42', 'alt' => '')) ?>
    <h1 aria-describedby="results-label">
      <?php if (isset($pager) && $pager->hasResults()): ?>
        <?php echo __('Showing %1% results', array('%1%' => $pager->getNbResults())) ?>
      <?php else: ?>
        <?php echo __('No results') ?>
      <?php endif; ?>
    </h1>
    <?php if (isset($pager) && $pager->hasResults()): ?>
      <span class="sub" id="results-label"><?php echo __('Newest additions') ?></span>
    <?php endif; ?>
  </div>

<?php end_slot() ?>

<?php slot('content') ?>

  <?php echo get_partial('search/updatesSearch', array(
    'form'         => $form,
    'show'         => $showForm)) ?>

  <?php if (isset($pager) && $pager->hasResults()): ?>

    <table class="table table-bordered table-striped sticky-enabled" id="clipboardButtonNode">
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

          <?php $doc = $result->getData() ?>

          <tr>

            <td>

              <?php if ('QubitInformationObject' == $className): ?>

                <?php echo link_to(render_title(truncate_text(get_search_i18n($doc, 'title', array('allowEmpty' => false)), 100)), array('slug' => $doc['slug'], 'module' => 'informationobject')) ?>
                <?php $status = (isset($doc['publicationStatusId'])) ? QubitTerm::getById($doc['publicationStatusId']) : null ?>
                <?php if (isset($status) && $status->id == QubitTerm::PUBLICATION_STATUS_DRAFT_ID): ?><span class="note2"><?php echo ' ('.$status->name.')' ?></span><?php endif; ?>

              <?php elseif ('QubitActor' == $className): ?>

                <?php $name = render_title(truncate_text(get_search_i18n($doc, 'authorizedFormOfName', array('allowEmpty' => false)), 100)) ?>
                <?php echo link_to($name, array('slug' => $doc['slug'], 'module' => 'actor')) ?>

              <?php elseif ('QubitFunction' == $className): ?>

                <?php $name = render_title(truncate_text(get_search_i18n($doc, 'authorizedFormOfName', array('allowEmpty' => false)), 100)) ?>
                <?php echo link_to($name, array('slug' => $doc['slug'], 'module' => 'function')) ?>

              <?php elseif ('QubitRepository' == $className): ?>

                <?php $name = render_title(truncate_text(get_search_i18n($doc, 'authorizedFormOfName', array('allowEmpty' => false)), 100)) ?>
                <?php echo link_to($name, array('slug' => $doc['slug'], 'module' => 'repository')) ?>

              <?php elseif ('QubitTerm' == $className): ?>

                <?php $name = render_title(truncate_text(get_search_i18n($doc, 'name', array('allowEmpty' => false)), 100)) ?>
                <?php echo link_to($name, array('slug' => $doc['slug'], 'module' => 'term')) ?>

              <?php endif; ?>

            </td>

            <?php if ('QubitInformationObject' == $className && 0 < sfConfig::get('app_multi_repository')): ?>
              <td>
                <?php if (null !== $repository = (isset($doc['repository'])) ? truncate_text(get_search_i18n($doc['repository'], 'authorizedFormOfName', array('allowEmpty' => false)), 100) : null): ?>
                  <?php echo $repository ?>
                <?php endif; ?>
              </td>
            <?php elseif('QubitTerm' == $className): ?>
              <td><?php echo truncate_text(get_search_i18n($doc, 'name', array('allowEmpty' => false)), 100) ?></td>
            <?php endif; ?>

            <td>
              <?php if ('CREATED_AT' != $form->getValue('dateOf')): ?>
                <?php echo format_date($doc['updatedAt'], 'f') ?>
              <?php else: ?>
                <?php echo format_date($doc['createdAt'], 'f') ?>
              <?php endif; ?>
            </td>

            <?php if ('QubitInformationObject' == $className || 'QubitActor' == $className || 'QubitRepository' == $className): ?>
              <td>
                <?php echo get_component('object', 'clipboardButton', array('slug' => $doc['slug'], 'wide' => true)) ?>
              </td>
            <?php endif; ?>

          </tr>

        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>

<?php end_slot() ?>

<?php slot('after-content') ?>
  <?php echo get_partial('default/pager', array('pager' => $pager)) ?>
<?php end_slot() ?>
