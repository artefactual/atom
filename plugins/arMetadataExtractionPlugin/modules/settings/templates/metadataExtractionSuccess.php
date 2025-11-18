<?php use_helper('Javascript') ?>

<h1><?php echo __('Metadata extraction settings') ?></h1>

<?php echo $form->renderGlobalErrors() ?>

<form action="<?php echo url_for(['module' => 'settings', 'action' => 'metadataExtraction']) ?>" method="post">

  <div id="content">
    
    <fieldset class="collapsible">
      <legend><?php echo __('Main settings') ?></legend>
      
      <div class="form-item">
        <?php echo $form['metadata_extraction_enabled']->renderLabel() ?>
        <?php echo $form['metadata_extraction_enabled']->renderError() ?>
        <div class="field">
          <?php echo $form['metadata_extraction_enabled'] ?> 
        </div>
        <div class="description">
          <?php echo __('Enable or disable automatic metadata extraction from uploaded digital objects') ?>
        </div>
      </div>
    </fieldset>

    <fieldset class="collapsible">
      <legend><?php echo __('Metadata types') ?></legend>
      
      <div class="form-item">
        <?php echo $form['extract_exif']->renderError() ?>
        <div class="field">
          <?php echo $form['extract_exif'] ?>
          <?php echo $form['extract_exif']->renderLabel(__('Extract EXIF metadata')) ?>
        </div>
        <div class="description">
          <?php echo __('Extract camera settings, date taken, and technical information from EXIF data') ?>
        </div>
      </div>

      <div class="form-item">
        <?php echo $form['extract_iptc']->renderError() ?>
        <div class="field">
          <?php echo $form['extract_iptc'] ?>
          <?php echo $form['extract_iptc']->renderLabel(__('Extract IPTC metadata')) ?>
        </div>
        <div class="description">
          <?php echo __('Extract headline, caption, keywords, and creator from IPTC data') ?>
        </div>
      </div>

      <div class="form-item">
        <?php echo $form['extract_xmp']->renderError() ?>
        <div class="field">
          <?php echo $form['extract_xmp'] ?>
          <?php echo $form['extract_xmp']->renderLabel(__('Extract XMP metadata')) ?>
        </div>
        <div class="description">
          <?php echo __('Extract descriptive metadata from Adobe XMP sidecar data') ?>
        </div>
      </div>
    </fieldset>

    <fieldset class="collapsible">
      <legend><?php echo __('Field update behavior') ?></legend>
      
      <div class="form-item">
        <?php echo $form['overwrite_title']->renderError() ?>
        <div class="field">
          <?php echo $form['overwrite_title'] ?>
          <?php echo $form['overwrite_title']->renderLabel(__('Always overwrite title')) ?>
        </div>
        <div class="description">
          <?php echo __('Overwrite existing titles with metadata (unchecked = only update if empty)') ?>
        </div>
      </div>

      <div class="form-item">
        <?php echo $form['overwrite_description']->renderError() ?>
        <div class="field">
          <?php echo $form['overwrite_description'] ?>
          <?php echo $form['overwrite_description']->renderLabel(__('Always overwrite description')) ?>
        </div>
        <div class="description">
          <?php echo __('Overwrite existing descriptions with metadata (unchecked = only update if empty)') ?>
        </div>
      </div>
    </fieldset>

    <fieldset class="collapsible">
      <legend><?php echo __('Additional features') ?></legend>
      
      <div class="form-item">
        <?php echo $form['auto_generate_keywords']->renderError() ?>
        <div class="field">
          <?php echo $form['auto_generate_keywords'] ?>
          <?php echo $form['auto_generate_keywords']->renderLabel(__('Auto-generate keywords')) ?>
        </div>
        <div class="description">
          <?php echo __('Automatically generate subject access points based on camera and technical data') ?>
        </div>
      </div>

      <div class="form-item">
        <?php echo $form['extract_gps_coordinates']->renderError() ?>
        <div class="field">
          <?php echo $form['extract_gps_coordinates'] ?>
          <?php echo $form['extract_gps_coordinates']->renderLabel(__('Extract GPS coordinates')) ?>
        </div>
        <div class="description">
          <?php echo __('Extract and store GPS location data from geotagged images') ?>
        </div>
      </div>

      <div class="form-item">
        <?php echo $form['add_technical_metadata']->renderError() ?>
        <div class="field">
          <?php echo $form['add_technical_metadata'] ?>
          <?php echo $form['add_technical_metadata']->renderLabel(__('Add technical metadata')) ?>
        </div>
        <div class="description">
          <?php echo __('Add camera settings and technical information to physical characteristics field') ?>
        </div>
      </div>
    </fieldset>

  </div>

  <section class="actions">
    <ul>
      <li><input class="c-btn c-btn--submit" type="submit" value="<?php echo __('Save') ?>"/></li>
      <li><?php echo link_to(__('Cancel'), ['module' => 'settings', 'action' => 'list'], ['class' => 'c-btn']) ?></li>
    </ul>
  </section>

</form>
