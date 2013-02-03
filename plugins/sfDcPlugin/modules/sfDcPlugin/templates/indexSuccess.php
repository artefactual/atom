<div class="row">

  <div class="span3">

    <div id="left-column">

      <?php echo get_component('informationobject', 'contextMenu') ?>

    </div>

  </div>

  <div class="span9">

    <div id="main-column">

      <h1><?php echo render_title($dc) ?></h1>

      <?php echo get_partial('informationobject/printPreviewBar', array('resource' => $resource)) ?>

      <?php if (isset($errorSchema)): ?>
        <div class="messages error">
          <ul>
            <?php foreach ($errorSchema as $error): ?>
              <li><?php echo $error ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <?php if (QubitInformationObject::ROOT_ID != $resource->parentId): ?>
        <?php echo include_partial('default/breadcrumb', array('resource' => $resource, 'objects' => $resource->getAncestors()->andSelf()->orderBy('lft'))) ?>
      <?php endif; ?>

      <div class="row">

        <div class="span7">

          <div id="content">

            <?php if (0 < count($resource->digitalObjects)): ?>
              <?php echo get_component('digitalobject', 'show', array('link' => $digitalObjectLink, 'resource' => $resource->digitalObjects[0], 'usageType' => QubitTerm::REFERENCE_ID)) ?>
            <?php endif; ?>

            <section id="elementsArea">

              <?php echo link_to_if(SecurityPriviliges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Elements area').'</h2>', array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('anchor' => 'mainArea', 'title' => __('Edit elements area'))) ?>

              <?php echo render_show(__('Identifier'), render_value($resource->identifier)) ?>

              <?php echo render_show(__('Title'), render_value($resource->getTitle(array('cultureFallback' => true)))) ?>

              <?php  foreach ($resource->getCreators() as $item): ?>
                <div class="field">
                  <h3><?php echo __('Creator') ?></h3>
                  <div>
                    <?php echo link_to(render_title($item), array($item, 'module' => 'actor')) ?><?php if (0 < strlen($value = $item->getDatesOfExistence(array('cultureFallback' => true)))): ?> <span class="note2">(<?php echo $value ?>)</span><?php endif; ?>
                  </div>
                </div>
              <?php endforeach; ?>

              <?php  foreach ($resource->getPublishers() as $item): ?>
                <div class="field">
                  <h3><?php echo __('Publisher') ?></h3>
                  <div>
                    <?php echo link_to(render_title($item), array($item, 'module' => 'actor')) ?><?php if ($value = $item->getDatesOfExistence(array('cultureFallback' => true))): ?> <span class="note2">(<?php echo $value ?>)</span><?php endif; ?>
                  </div>
                </div>
              <?php endforeach; ?>

              <?php  foreach ($resource->getContributors() as $item): ?>
                <div class="field">
                  <h3><?php echo __('Contributor') ?></h3>
                  <div>
                    <?php echo link_to(render_title($item), array($item, 'module' => 'actor')) ?><?php if ($value = $item->getDatesOfExistence(array('cultureFallback' => true))): ?> <span class="note2">(<?php echo $value ?>)</span><?php endif; ?>
                  </div>
                </div>
              <?php endforeach; ?>

              <?php echo get_partial('informationobject/dates', array('resource' => $resource)) ?>

              <?php foreach ($resource->getSubjectAccessPoints() as $item): ?>
                <?php echo render_show(__('Subject'), link_to($item->term, array($item->term, 'module' => 'term', 'action' => 'browseTerm'))) ?>
              <?php endforeach; ?>

              <?php echo render_show(__('Description'), render_value($resource->getScopeAndContent(array('cultureFallback' => true)))) ?>

              <?php foreach ($dc->type as $item): ?>
                <?php echo render_show(__('Type'), $item) ?>
              <?php endforeach; ?>

              <?php foreach ($dc->format as $item): ?>
                <?php echo render_show(__('Format'), render_value($item)) ?>
              <?php endforeach; ?>

              <?php echo render_show(__('Source'), render_value($resource->getLocationOfOriginals(array('cultureFallback' => true)))) ?>

              <?php foreach ($resource->language as $code): ?>
                <?php echo render_show(__('Language'), format_language($code)) ?>
              <?php endforeach; ?>

              <?php echo render_show_repository(__('Relation (isLocatedAt)'), $resource) ?>

              <?php foreach ($dc->coverage as $item): ?>
                <?php echo render_show(__('Coverage (spatial)'), link_to(render_title($item), array($item, 'module' => 'term', 'action' => 'browseTerm'))) ?>
              <?php endforeach; ?>

              <?php echo render_show(__('Rights'), render_value($resource->getAccessConditions(array('cultureFallback' => true)))) ?>

            </section> <!-- /section#elementsArea -->

            <?php if ($sf_user->isAuthenticated()): ?>

              <section id="rightsArea">

                <?php echo link_to_if(QubitAcl::check($resource, 'update'), '<h2>'.__('Rights area').'</h2>', array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('anchor' => 'rightsArea', 'title' => __('Edit rights area'))) ?>

                <?php echo get_component('right', 'relatedRights', array('resource' => $resource)) ?>

              </section> <!-- /section#rightsArea -->

            <?php endif; ?>

            <?php if (0 < count($resource->digitalObjects)): ?>

              <?php echo get_partial('digitalobject/metadata', array('resource' => $resource->digitalObjects[0])) ?>

              <?php echo get_partial('digitalobject/rights', array('resource' => $resource->digitalObjects[0])) ?>

            <?php endif; ?>

            <section id="accessionArea">

              <h2><?php echo __('Accession area') ?></h2>

              <?php echo get_component('informationobject', 'accessions', array('resource' => $resource)) ?>

            </section> <!-- /section#accessionArea -->

          </div>

        </div>

        <div class="span2">

          <div id="right-column">

            <?php echo get_partial('informationobject/actionIcons', array('resource' => $resource)) ?>

          </div>

        </div>

      </div>

      <div class="row">

        <div class="span9">

          <?php echo get_partial('informationobject/actions', array('resource' => $resource)) ?>

        </div>

      </div>

    </div>

  </div>

</div>
