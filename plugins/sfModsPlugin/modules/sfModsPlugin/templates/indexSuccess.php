<div class="row">

  <div class="span3">

    <div id="left-column">

      <?php echo get_component('informationobject', 'contextMenu') ?>

    </div>

  </div>

  <div class="span9">

    <div id="main-column">

      <h1><?php echo render_title($mods) ?></h1>

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

            <section id="elementsArea">

              <?php echo link_to_if(SecurityPriviliges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Elements area').'</h2>', array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('anchor' => 'mainArea', 'title' => __('Edit elements area'))) ?>

              <?php if (0 < count($resource->digitalObjects)): ?>
                <?php echo get_component('digitalobject', 'show', array('link' => $digitalObjectLink, 'resource' => $resource->digitalObjects[0], 'usageType' => QubitTerm::REFERENCE_ID)) ?>
              <?php endif; ?>

              <?php echo render_show(__('Identifier'), render_value($resource->identifier)) ?>

              <?php echo render_show(__('Title'), render_value($resource->getTitle(array('cultureFallback' => true)))) ?>

              <?php echo get_partial('informationobject/nameAccessPoints', array('resource' => $resource)) ?>

              <?php echo get_partial('informationobject/dates', array('resource' => $resource)) ?>

              <?php foreach ($mods->typeOfResource as $item): ?>
                <?php echo render_show(__('Type of resource'), $item->term) ?>
              <?php endforeach; ?>

              <?php foreach ($resource->language as $code): ?>
                <?php echo render_show(__('Language'), format_language($code)) ?>
              <?php endforeach; ?>

              <?php if (0 < count($resource->digitalObjects)): ?>
                <?php echo render_show(__('Internet media type'), $resource->digitalObjects[0]->mimeType) ?>
              <?php endif; ?>

              <?php foreach ($resource->getSubjectAccessPoints() as $item): ?>
                <?php echo render_show(__('Subject'), link_to($item->term, array($item->term, 'module' => 'term', 'action' => 'browseTerm'))) ?>
              <?php endforeach; ?>

              <?php echo render_show(__('Access condition'), render_value($resource->getAccessConditions(array('cultureFallback' => true)))) ?>

              <?php if (0 < count($resource->digitalObjects)): ?>
                <?php if (QubitTerm::EXTERNAL_URI_ID == $resource->digitalObjects[0]->usageId): ?>
                  <?php echo render_show(__('URL'), link_to(null, $resource->digitalObjects[0]->path)) ?>
                <?php else: ?>
                  <?php echo render_show(__('URL'), link_to(null, public_path($resource->digitalObjects[0]->getFullPath(), true))) ?>
                <?php endif; ?>
              <?php endif; ?>

              <div class="field">
                <h3><?php echo __('Physical location') ?></h3>
                <div>
                  <?php if (isset($resource->repository)): ?>

                    <?php if (isset($resource->repository->identifier)): ?>
                      <?php echo $resource->repository->identifier ?> -
                    <?php endif; ?>

                    <?php echo link_to(render_title($resource->repository), array($resource->repository, 'module' => 'repository')) ?>

                    <?php if (null !== $contact = $resource->repository->getPrimaryContact()): ?>

                      <?php if (isset($contact->city)): ?>
                        <?php echo $contact->city ?>
                      <?php endif; ?>

                      <?php if (isset($contact->region)): ?>
                        <?php echo $contact->region ?>
                      <?php endif; ?>

                      <?php if (isset($contact->countryCode)): ?>
                        <?php echo format_country($contact->countryCode) ?>
                      <?php endif; ?>

                    <?php endif; ?>

                  <?php endif; ?>
                </div>
              </div>

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

            </section> <!-- /section#elementsArea -->

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
