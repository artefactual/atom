<?php decorate_with('layout_1col'); ?>
<?php use_helper('Date'); ?>
<?php use_helper('Text'); ?>

<?php slot('title'); ?>
  <div class="multiline-header d-flex align-items-center mb-3">
    <i class="fas fa-3x fa-newspaper me-3" aria-hidden="true"></i>
    <div class="d-flex flex-column">
      <h1 class="mb-0" aria-describedby="heading-label">
        <?php if (isset($pager) && $pager->getNbResults()) { ?>
          <?php echo __('Showing %1% results', ['%1%' => $pager->getNbResults()]); ?>
        <?php } else { ?>
          <?php echo __('No results found'); ?>
        <?php } ?>
      </h1>
      <span class="small" id="heading-label">
        <?php echo __('Newest additions'); ?>
      </span>
    </div>
  </div>
<?php end_slot(); ?>

<?php slot('content'); ?>
  <?php echo get_partial('search/updatesSearch', [
      'form' => $form,
      'show' => $showForm,
      'user' => $user,
  ]); ?>

  <div class="table-responsive mb-3">
    <?php if (
        'QubitInformationObject' == $className
        && sfConfig::get('app_audit_log_enabled', false)
    ) { ?>

      <table class="table table-bordered mb-0">
        <thead>
          <tr>
            <th style="width: 40%">
              <?php echo __('Title'); ?>
            </th>
            <th style="width: 40%">
              <?php echo __('Repository'); ?>
            </th>
            <?php if ('CREATED_AT' != $form->getValue('dateOf')) { ?>
              <th style="width: 20%">
                <?php echo __('Updated'); ?>
              </th>
            <?php } else { ?>
              <th style="width: 20%">
                <?php echo __('Created'); ?>
              </th>
            <?php } ?>
            <th>
              <a href="#" class="clipboard-all">
                <?php echo __('All'); ?>
              </a>
              <span>/</span>
              <a href="#" class="clipboard-none">
                <?php echo __('None'); ?>
              </a>
            </th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($pager->getResults() as $result) { ?>
          <?php $io = QubitInformationObject::getById($result->objectId); ?>
          <tr>
            <td>
              <?php echo link_to(render_title($io), [
                  'slug' => $io->slug,
                  'module' => 'informationobject',
              ]); ?>
            </td>
            <td>
              <?php if (!empty($io->repository)) { ?>
                <?php echo link_to(render_title($io->repository->authorizedFormOfName), [
                    'slug' => $io->repository->slug,
                    'module' => 'repository',
                ]); ?>
              <?php } ?>
            </td>
            <td>
              <?php echo format_date($result->createdAt, 'f'); ?>
            </td>
            <td>
              <?php echo get_component('clipboard', 'button', [
                  'slug' => $io->slug,
                  'wide' => true,
                  'type' => 'informationObject',
              ]); ?>
            </td>
          </tr>
        <?php } ?>
        </tbody>
      </table>

    <?php } elseif (isset($pager) && $pager->getNbResults()) { ?>

      <table class="table table-bordered mb-0">
        <thead>
          <tr>
            <?php if (
                'QubitInformationObject' == $className
                && 0 < sfConfig::get('app_multi_repository')
            ) { ?>
              <th style="width: 40%">
                <?php echo __($nameColumnDisplay); ?>
              </th>
              <th style="width: 40%">
                <?php echo __('Repository'); ?>
              </th>
            <?php } elseif ('QubitTerm' == $className) { ?>
              <th style="width: 40%">
                <?php echo __($nameColumnDisplay); ?>
              </th>
              <th style="width: 40%">
                <?php echo __('Taxonomy'); ?>
              </th>
            <?php } else { ?>
              <th style="width: 80%">
                <?php echo __($nameColumnDisplay); ?>
              </th>
            <?php } ?>
            <?php if ('CREATED_AT' != $form->getValue('dateOf')) { ?>
              <th style="width: 20%">
                <?php echo __('Updated'); ?>
              </th>
            <?php } else { ?>
              <th style="width: 20%">
                <?php echo __('Created'); ?>
              </th>
            <?php } ?>
            <?php if (
                'QubitInformationObject' == $className
                || 'QubitActor' == $className
                || 'QubitRepository' == $className
            ) { ?>
              <th>
                <a href="#" class="clipboard-all">
                  <?php echo __('All'); ?>
                </a>
                <span>/</span>
                <a href="#" class="clipboard-none">
                  <?php echo __('None'); ?>
                </a>
              </th>
            <?php } ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($pager->getResults() as $result) { ?>
            <?php $doc = $result->getData(); ?>
            <tr>
              <td>
                <?php if ('QubitInformationObject' == $className) { ?>

                  <?php echo link_to(
                      render_title(get_search_i18n($doc, 'title', ['allowEmpty' => false])),
                      ['slug' => $doc['slug'], 'module' => 'informationobject']
                  ); ?>
                  <?php $status = (isset($doc['publicationStatusId']))
                      ? QubitTerm::getById($doc['publicationStatusId'])
                      : null;
                  ?>
                  <?php if (isset($status) && QubitTerm::PUBLICATION_STATUS_DRAFT_ID == $status->id) { ?>
                    <span class="text-muted">
                      <?php echo ' ('.render_value_inline($status).')'; ?>
                    </span>
                  <?php } ?>

                <?php } elseif ('QubitActor' == $className) { ?>

                  <?php $name = render_title(
                      get_search_i18n($doc, 'authorizedFormOfName', ['allowEmpty' => false])
                  ); ?>
                  <?php echo link_to($name, ['slug' => $doc['slug'], 'module' => 'actor']); ?>

                <?php } elseif ('QubitFunctionObject' == $className) { ?>

                  <?php $name = render_title(
                      get_search_i18n($doc, 'authorizedFormOfName', ['allowEmpty' => false])
                  ); ?>
                  <?php echo link_to($name, ['slug' => $doc['slug'], 'module' => 'function']); ?>

                <?php } elseif ('QubitRepository' == $className) { ?>

                  <?php $name = render_title(
                      get_search_i18n($doc, 'authorizedFormOfName', ['allowEmpty' => false])
                  ); ?>
                  <?php echo link_to($name, ['slug' => $doc['slug'], 'module' => 'repository']); ?>

                <?php } elseif ('QubitTerm' == $className) { ?>

                  <?php $name = render_title(get_search_i18n($doc, 'name', ['allowEmpty' => false])); ?>
                  <?php echo link_to($name, ['slug' => $doc['slug'], 'module' => 'term']); ?>

                <?php } ?>

              </td>

              <?php if (
                  'QubitInformationObject' == $className
                  && 0 < sfConfig::get('app_multi_repository')
              ) { ?>
                <td>
                  <?php if (
                      null !== $repository = (isset($doc['repository']))
                          ? render_title(get_search_i18n(
                              $doc['repository'],
                              'authorizedFormOfName',
                              ['allowEmpty' => false]
                          ))
                          : null
                  ) { ?>
                    <?php echo $repository; ?>
                  <?php } ?>
                </td>
              <?php } elseif ('QubitTerm' == $className) { ?>
                <td>
                  <?php echo render_title(get_search_i18n($doc, 'name', ['allowEmpty' => false])); ?>
                </td>
              <?php } ?>

              <td>
                <?php if ('CREATED_AT' != $form->getValue('dateOf')) { ?>
                  <?php echo format_date($doc['updatedAt'], 'f'); ?>
                <?php } else { ?>
                  <?php echo format_date($doc['createdAt'], 'f'); ?>
                <?php } ?>
              </td>

              <?php if ('QubitInformationObject' == $className) { ?>
                <td>
                  <?php echo get_component('clipboard', 'button', [
                      'slug' => $doc['slug'],
                      'wide' => true,
                      'type' => 'informationObject',
                  ]); ?>
                </td>
              <?php } elseif ('QubitActor' == $className) { ?>
                <td>
                  <?php echo get_component('clipboard', 'button', [
                      'slug' => $doc['slug'],
                      'wide' => true,
                      'type' => 'actor',
                  ]); ?>
                </td>
              <?php } elseif ('QubitRepository' == $className) { ?>
                <td>
                  <?php echo get_component('clipboard', 'button', [
                      'slug' => $doc['slug'],
                      'wide' => true,
                      'type' => 'repository',
                  ]); ?>
                </td>
              <?php } ?>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    <?php } ?>
  </div>
<?php end_slot(); ?>

<?php slot('after-content'); ?>
  <?php echo get_partial('default/pager', ['pager' => $pager]); ?>
<?php end_slot(); ?>
