<div class="accordion-item">
  <h2 class="accordion-header" id="heading-<?php echo $usageId; ?>">
    <button
      class="accordion-button collapsed"
      type="button"
      data-bs-toggle="collapse"
      data-bs-target="#collapse-<?php echo $usageId; ?>"
      aria-expanded="false"
      aria-controls="collapse-<?php echo $usageId; ?>">
      <?php echo __('%1%', ['%1%' => QubitTerm::getById($usageId)]); ?>
    </button>
  </h2>
  <div
    id="collapse-<?php echo $usageId; ?>"
    class="accordion-collapse collapse"
    aria-labelledby="heading-<?php echo $usageId; ?>">
    <div class="accordion-body">
      <div class="table-responsive mb-3">
        <table class="table table-bordered mb-0">
          <thead class="table-light">
            <tr>
              <th style="width: 30%">
                <?php echo __('Language'); ?>
              </th>
              <th style="width: 50%">
                <?php echo __('Filename'); ?>
              </th>
              <th style="width: 20%">
                <?php echo __('Filesize'); ?>
              </th>
              <th>
                <span class="visually-hidden"><?php echo __('Actions'); ?></span>
              </th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($subtitles as $subtitle) { ?>
              <tr>
                <td>
                  <?php echo format_language($subtitle->language); ?>
                </td>
                <td>
                  <?php echo render_value_inline($subtitle->name); ?>
                </td>
                <td>
                  <?php echo hr_filesize($subtitle->byteSize); ?>
                </td>
                <td class="text-nowrap">
                  <a
                    href="<?php echo $subtitle->getFullPath(); ?>"
                    class="btn atom-btn-white me-1">
                    <i class="fas fa-fw fa-eye" aria-hidden="true"></i>
                    <span class="visually-hidden"><?php echo __('View file'); ?></span>
                  </a>
                  <a
                    href="<?php echo url_for([
                        $subtitle,
                        'module' => 'digitalobject',
                        'action' => 'delete',
                    ]); ?>"
                    class="btn atom-btn-white">
                    <i class="fas fa-fw fa-times" aria-hidden="true"></i>
                    <span class="visually-hidden"><?php echo __('Delete'); ?></span>
                  </a>
                </td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>

      <div class="row">
        <div class="col-md-6">
          <?php echo render_field($form["trackFile_{$usageId}"]->label(__(
              'Select a file to upload (.vtt|.srt)'
          ))); ?>
        </div>
        <div class="col-md-6">    
          <?php echo render_field($form["lang_{$usageId}"]->label(__('Language'))); ?>
        </div>
      </div>
    </div>
  </div>
</div>
