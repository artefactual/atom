<h1>
  <?php echo image_tag('/plugins/qtDominionPlugin/images/icons-large/icon-new.png', array('width' => '42', 'height' => '42')) ?>
  <?php echo __('Browse newest additions') ?>
</h1>

<div class="row">

  <div class="span3" id="left-column">

    <div class="section" id="search-updates-form">

      <?php echo $form->renderFormTag(url_for(array('module' => 'search', 'action' => 'updates')), array('method' => 'get')) ?>

        <?php echo $form->renderHiddenFields() ?>

        <?php echo $form->className
          ->label('Type')
          ->renderRow() ?>

        <?php if (false): ?>
        <?php echo __('Date range') ?>
        <?php echo $form->dateStart->renderError() ?>
        <?php echo $form->dateEnd->renderError() ?>
        <?php echo __('%1% to %2%', array(
          '%1%' => $form->dateStart->render(),
          '%2%' => $form->dateEnd->render())) ?>
        <?php endif; ?>

        <?php echo $form->dateOf->renderRow() ?>

        <?php echo $form->publicationStatus
          ->label(__('Publication status <span class="note2">(%1% only)</span>', array('%1%' => sfConfig::get('app_ui_label_informationobject'))))
          ->renderRow() ?>

        <div class="form-actions">
          <button type="submit" class="btn"><?php echo __('Search') ?></button>
        </div>

      </form>

    </div>

  </div>

  <div class="span9" id="content">

    <?php if ($form->isValid()): ?>

      <div class="section">

        <table class="table table-bordered">
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
            </tr>
          </thead><tbody>
            <?php foreach ($pager->getResults() as $result): ?>

              <tr class="<?php echo 0 == @++$row % 2 ? 'even' : 'odd' ?>">

                <td>
                  <?php if ('QubitInformationObject' == $className): ?>
                    <?php $title = render_title($result->getTitle(array('cultureFallback' => true))) ?>
                    <?php echo link_to($title, array($result, 'module' => 'informationobject')) ?>
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

              </tr>

            <?php endforeach; ?>
          </tbody>
        </table>

        <?php echo get_partial('default/pager', array('pager' => $pager)) ?>

      </div>

    <?php endif; ?>

  </div>

</div>
