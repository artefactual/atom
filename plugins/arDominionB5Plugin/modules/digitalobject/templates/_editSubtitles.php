<div class="accordion-item">
  <h2 class="accordion-header" id="heading-<?php echo $usageId; ?>">
    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?php echo $usageId; ?>" aria-expanded="false" aria-controls="collapse-<?php echo $usageId; ?>">
      <?php echo __('%1%', ['%1%' => QubitTerm::getById($usageId)]); ?>
    </button>
  </h2>
  <div id="collapse-<?php echo $usageId; ?>" class="accordion-collapse collapse" aria-labelledby="heading-<?php echo $usageId; ?>" data-bs-parent="#digitalobject-edit">
    <div class="accordion-body">
      <?php foreach ($subtitles as $subtitle) { ?>
        <table class="table table-bordered">
          <thead>
            <tr><th><?php echo format_language($subtitle->language); ?></th></tr>
          </thead>
          <tbody>
            <tr>
              <td>
              
                <div class="digital-object-preview">
                  <a href="<?php echo $subtitle->getFullPath(); ?>"><?php echo __('View file'); ?></a>
                </div>
              
                <div>
                  <?php echo render_show(__('Filename'), render_value($subtitle->name)); ?>
                  <?php echo render_show(__('Filesize'), hr_filesize($subtitle->byteSize)); ?>
                  <?php echo link_to(__('Delete'), [$subtitle, 'module' => 'digitalobject', 'action' => 'delete'], ['class' => 'delete']); ?>
                </div>
            
              </td>
            </tr>
          </tbody>
        </table>
      <?php } ?>

      <table class="table table-bordered">
        <tr>
          <td>
            <?php echo $form["trackFile_{$usageId}"]
                ->label(__('Select a file to upload (.vtt|.srt)'))
                ->renderRow(); ?>
                    
            <?php echo $form["lang_{$usageId}"]
                ->label(__('Language'))
                ->renderRow(); ?>
          </td>
        </tr>
      </table>
    </div>
  </div>
</div>
