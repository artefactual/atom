<div id="main-column" class="span9 offset3">

  <div id="headline">
    <h1><?php echo render_title($resource) ?></h1>
  </div>

  <!--
  <?php if (isset($errorSchema)): ?>
    <div class="alert alert-info">
      <a class="close" data-dismiss="alert" href="#">×</a>
      <h4 class="alert-heading"><?php echo __('Warning!') ?></h4>
      <ul>
        <?php foreach ($errorSchema as $error): ?>
          <li><?php echo $error ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>
  -->

  <?php if (isset($latitude) && isset($longitude)): ?>
    <div class="row" id="front-map">
      <div class="span7">
        <?php echo image_tag(sprintf('http://maps.googleapis.com/maps/api/staticmap?zoom=16&size=720x180&maptype=roadmap&sensor=false&markers=color:red|label:S|%s,%s', $latitude, $longitude)) ?>
        <?php // echo image_tag(sprintf('http://ojw.dev.openstreetmap.org/StaticMap/?lat=%s&lon=%s&z=10&w=720&h=200&mode=Export&show=1', $latitude, $longitude)) ?>
      </div>
    </div>
  <?php endif; ?>

  <div class="row">

    <div class="span7" id="content">

      <div class="section" id="identifyArea">

        <?php echo link_to_if(SecurityPriviliges::editCredentials($sf_user, 'repository'), '<h2>'.__('Identity area').'</h2>', array($resource, 'module' => 'repository', 'action' => 'edit'), array('anchor' => 'identityArea', 'title' => __('Edit identity area'))) ?>

        <?php echo render_show(__('Identifier'), render_value($resource->identifier)) ?>

        <?php echo render_show(__('Authorized form of name'), render_value($resource)) ?>

        <div class="field">
          <h3><?php echo __('Parallel form(s) of name') ?></h3>
          <div>
            <ul>
              <?php foreach ($resource->getOtherNames(array('typeId' => QubitTerm::PARALLEL_FORM_OF_NAME_ID)) as $item): ?>
                <li><?php echo render_value($item->__toString()) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>

        <div class="field">
          <h3><?php echo __('Other form(s) of name') ?></h3>
          <div>
            <ul>
              <?php foreach ($resource->getOtherNames(array('typeId' => QubitTerm::OTHER_FORM_OF_NAME_ID)) as $item): ?>
                <li><?php echo render_value($item->__toString()) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>

        <?php foreach ($resource->getTermRelations(QubitTaxonomy::REPOSITORY_TYPE_ID) as $item): ?>
          <?php echo render_show(__('Type'), render_value($item->term)) ?>
        <?php endforeach; ?>

      </div>

      <div class="section" id="contactArea">

        <?php echo link_to_if(SecurityPriviliges::editCredentials($sf_user, 'repository'), '<h2>'.__('Contact area').'</h2>', array($resource, 'module' => 'repository', 'action' => 'edit'), array('anchor' => 'contactArea', 'title' => __('Edit contact area'))) ?>

        <?php foreach ($resource->contactInformations as $contactItem): ?>
          <?php echo get_partial('contactinformation/contactInformation', array('contactInformation' => $contactItem)) ?>
        <?php endforeach; ?>

      </div>

      <div class="section" id="descriptionArea">

        <?php echo link_to_if(SecurityPriviliges::editCredentials($sf_user, 'repository'), '<h2>'.__('Description area').'</h2>', array($resource, 'module' => 'repository', 'action' => 'edit'), array('anchor' => 'descriptionArea', 'title' => __('Edit description area'))) ?>

        <?php echo render_show(__('History'), render_value($resource->getHistory(array('cultureFallback' => true)))) ?>

        <?php echo render_show(__('Geographical and cultural context'), render_value($resource->getGeoculturalContext(array('cultureFallback' => true)))) ?>

        <?php echo render_show(__('Mandates/Sources of authority'), render_value($resource->getMandates(array('cultureFallback' => true)))) ?>

        <?php echo render_show(__('Administrative structure'), render_value($resource->getInternalStructures(array('cultureFallback' => true)))) ?>

        <?php echo render_show(__('Records management and collecting policies'), render_value($resource->getCollectingPolicies(array('cultureFallback' => true)))) ?>

        <?php echo render_show(__('Buildings'), render_value($resource->getBuildings(array('cultureFallback' => true)))) ?>

        <?php echo render_show(__('Holdings'), render_value($resource->getHoldings(array('cultureFallback' => true)))) ?>

        <?php echo render_show(__('Finding aids, guides and publications'), render_value($resource->getFindingAids(array('cultureFallback' => true)))) ?>

      </div>

      <div class="section" id="accessArea">

        <?php echo link_to_if(SecurityPriviliges::editCredentials($sf_user, 'repository'), '<h2>'.__('Access area').'</h2>', array($resource, 'module' => 'repository', 'action' => 'edit'), array('anchor' => 'accessArea', 'title' => __('Edit access area'))) ?>

        <?php echo render_show(__('Opening times'), render_value($resource->getOpeningTimes(array('cultureFallback' => true)))) ?>

        <?php echo render_show(__('Access conditions and requirements'), render_value($resource->getAccessConditions(array('cultureFallback' => true)))) ?>

        <?php echo render_show(__('Accessibility'), render_value($resource->getDisabledAccess(array('cultureFallback' => true)))) ?>

      </div>

      <div class="section" id="servicesArea">

        <?php echo link_to_if(SecurityPriviliges::editCredentials($sf_user, 'repository'), '<h2>'.__('Services area').'</h2>', array($resource, 'module' => 'repository', 'action' => 'edit'), array('anchor' => 'servicesArea', 'title' => __('Edit services area'))) ?>

        <?php echo render_show(__('Research services'), render_value($resource->getResearchServices(array('cultureFallback' => true)))) ?>

        <?php echo render_show(__('Reproduction services'), render_value($resource->getReproductionServices(array('cultureFallback' => true)))) ?>

        <?php echo render_show(__('Public areas'), render_value($resource->getPublicFacilities(array('cultureFallback' => true)))) ?>

      </div>

      <div class="section" id="controlArea">

        <?php echo link_to_if(SecurityPriviliges::editCredentials($sf_user, 'repository'), '<h2>'.__('Control area').'</h2>', array($resource, 'module' => 'repository', 'action' => 'edit'), array('anchor' => 'controlArea', 'title' => __('Edit control area'))) ?>

        <?php echo render_show(__('Description identifier'), render_value($resource->descIdentifier)) ?>

        <?php echo render_show(__('Institution identifier'), render_value($resource->getDescInstitutionIdentifier(array('cultureFallback' => true)))) ?>

        <?php echo render_show(__('Rules and/or conventions used'), render_value($resource->getDescRules(array('cultureFallback' => true)))) ?>

        <?php echo render_show(__('Status'), render_value($resource->descStatus)) ?>

        <?php echo render_show(__('Level of detail'), render_value($resource->descDetail)) ?>

        <?php echo render_show(__('Dates of creation, revision and deletion'), render_value($resource->getDescRevisionHistory(array('cultureFallback' => true)))) ?>

        <div class="field">
          <h3><?php echo __('Language(s)') ?></h3>
          <div>
            <ul>
              <?php foreach ($resource->language as $code): ?>
                <li><?php echo format_language($code) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>

        <div class="field">
          <h3><?php echo __('Script(s)') ?></h3>
          <div>
            <ul>
              <?php foreach ($resource->script as $code): ?>
                <li><?php echo format_script($code) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>

        <?php echo render_show(__('Sources'), render_value($resource->getDescSources(array('cultureFallback' => true)))) ?>

        <?php echo render_show(__('Maintenance notes'), render_value($isdiah->maintenanceNotes)) ?>

      </div>

    </div>

    <div class="span2" id="right-column">

      <h3><?php echo __('Primary contact') ?></h3>
      <p>Charles Xavier<br>1293 West Broadway<br>Vancouver, BC, V6X 3X3<br>Canada</p>
      <div><a href="#" class="widebtn">Send Email</a></div>
      <div><a href="#" class="widebtn">Website</a></div>

      <h3><?php echo __('Opening times') ?></h3>
      <p>Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet.</p>

      <h3><?php echo __('Services') ?></h3>
      <p>Research Services: sit amet. Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet.</p>
    </div>

    </div>

  </div>

</div>

<?php if (QubitAcl::check($resource, array('update', 'delete', 'create'))): ?>
  <div class="actions section">

    <h2 class="element-invisible"><?php echo __('Actions') ?></h2>

    <div class="content">
      <ul class="clearfix links">
        <?php if (QubitAcl::check($resource, 'update')): ?>
        <li><?php echo link_to(__('Edit'), array($resource, 'module' => 'repository', 'action' => 'edit'), array('title' => __('Edit'))) ?></li>
        <?php endif; ?>
        <?php if (QubitAcl::check($resource, 'delete')): ?>
        <li><?php echo link_to(__('Delete'), array($resource, 'module' => 'repository', 'action' => 'delete'), array('class' => 'delete', 'title' => __('Delete'))) ?></li>
        <?php endif; ?>
        <?php if (QubitAcl::check($resource, 'create')): ?>
        <li><?php echo link_to(__('Add new'), array('module' => 'repository', 'action' => 'add'), array('title' => __('Add new'))) ?></li>
        <?php endif; ?>
      </ul>
    </div>

  </div>
<?php endif; ?>
