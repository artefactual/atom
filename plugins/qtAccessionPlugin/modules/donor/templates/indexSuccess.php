<h1><?php echo __('View donor') ?></h1>

<h1 class="label"><?php echo link_to_if(QubitAcl::check($resource, 'update'), render_title($resource), array($resource, 'module' => 'donor', 'action' => 'edit'), array('title' => __('Edit donor'))) ?></h1>

<?php if (isset($errorSchema)): ?>
  <div class="messages error">
    <ul>
      <?php foreach ($errorSchema as $error): ?>
        <li><?php echo $error ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<?php echo render_show(__('Authorized form of name'), render_value($resource->getAuthorizedFormOfName(array('cultureFallback' => true)))) ?>

<div class="section" id="contactArea">

  <h2><?php echo __('Contact area') ?></h2>

  <?php foreach ($resource->contactInformations as $contactItem): ?>
    <?php echo get_partial('contactinformation/contactInformation', array('contactInformation' => $contactItem)) ?>
  <?php endforeach; ?>

</div> <!-- /.section#contactArea -->

<div class="section" id="accessionArea">

  <h2><?php echo __('Accession area') ?></h2>

  <div class="field">

    <h3><?php echo __('Related accession(s)') ?></h3>

    <div>
      <ul>
        <?php foreach (QubitRelation::getRelationsByObjectId($resource->id, array('typeId' => QubitTerm::DONOR_ID)) as $item): ?>
          <li><?php echo link_to(render_title($item->subject), array($item->subject, 'module' => 'accession')) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>

  </div>

</div> <!-- /.section#accessionArea -->

<div class="actions section">

  <h2 class="element-invisible"><?php echo __('Actions') ?></h2>

  <div class="content">
    <ul class="clearfix links">

      <?php if (QubitAcl::check($resource, 'update')): ?>
        <li><?php echo link_to(__('Edit'), array($resource, 'module' => 'donor', 'action' => 'edit'), array('title' => __('Edit'))) ?></li>
      <?php endif; ?>

      <?php if (QubitAcl::check($resource, 'delete')): ?>
        <li><?php echo link_to(__('Delete'), array($resource, 'module' => 'donor', 'action' => 'delete'), array('class' => 'delete', 'title' => __('Delete'))) ?></li>
      <?php endif; ?>

      <?php if (QubitAcl::check($resource, 'create')): ?>
        <li><?php echo link_to(__('Add new'), array('module' => 'donor', 'action' => 'add'), array('title' => __('Add new'))) ?></li>
      <?php endif; ?>

    </ul>
  </div>

</div>
