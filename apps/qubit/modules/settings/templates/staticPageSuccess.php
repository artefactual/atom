<?php decorate_with('layout_2col.php'); ?>

<?php slot('sidebar'); ?>

<?php echo get_component('settings', 'menu'); ?>

<?php end_slot(); ?>

<?php slot('title'); ?>
<h1>
  <?php echo __('Static page'); ?>
</h1>
<?php end_slot(); ?>

<?php slot('content'); ?>

<?php echo $form->renderGlobalErrors(); ?>

<?php echo $form->renderFormTag(url_for(['module' => 'settings', 'action' => 'staticPage'])); ?>

<?php echo $form->renderHiddenFields(); ?>

<div class="accordion mb-3">
  <div class="accordion-item">
    <h2 class="accordion-header" id="static-img-upload-heading">
      <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#static-img-upload-collapse"
        aria-expanded="true" aria-controls="static-img-upload-collapse">
        <?php echo __('Upload static page images'); ?>
      </button>
    </h2>

    <div id="static-img-upload-collapse" class="accordion-collapse collapse show" aria-labelledby="sending-heading">

      <div class="alert alert-info m-3 mb-0">
        <p><?php echo __('Static page images must be of PNG, JPG, JPEG, SVG, or WebP format.'); ?></p>
      </div>

      <div class="accordion-body">
        <?php echo render_field($form->staticImgUpload->label(__('Upload static page images'))); ?>
      </div>
    </div>
  </div>

  <div class="accordion-item">
    <h2 class="accordion-header" id="static-img-count-heading">
      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#static-img-count-collapse"
        aria-expanded="false" aria-controls="static-img-count-collapse">
        <?php echo __('Static page images list'); ?>
      </button>
    </h2>

    <div id="static-img-count-collapse" class="accordion-collapse collapse" aria-labelledby="sending-heading">
      <?php if (isset($staticImgs) && count($staticImgs) > 0) { ?>
        <div class="accordion-body">
          <ul>
            <?php foreach ($staticImgs as $img) { ?>
              <?php if (str_starts_with($img, sfConfig::get('sf_web_dir'))) {
                $img = substr($img, strlen(sfConfig::get('sf_web_dir')));
              } ?>
              <li>
                <a href="<?php echo url_for($img); ?>" target="_blank"><?php echo $img; ?></a>
              </li>
            <?php } ?>
          </ul>
        </div>
      <?php } ?>
    </div>
  </div>
</div>

  <section class="actions">
    <input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Save'); ?>" />
  </section>

  </form>

  <?php end_slot(); ?>