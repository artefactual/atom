<?php decorate_with('layout_1col') ?>

<?php if ($sf_request->isMethod('get')): ?>
  <?php slot('title') ?>
    <div class="multiline-header">
      <?php echo image_tag('/images/icons-large/icon-archival.png', array('alt' => '')) ?>
      <h1 aria-describedby="results-label"><?php echo __('Loading ...') ?></h1>
      <span class="sub" id="results-label"><?php echo __('Clipboard') ?></span>
    </div>
  <?php end_slot() ?>

  <?php slot('content', ' ') ?>
<?php else: ?>
  <?php slot('title') ?>
    <?php echo get_partial('default/printPreviewBar') ?>

    <div class="multiline-header">
      <?php echo image_tag('/images/icons-large/icon-archival.png', array('alt' => '')) ?>
      <h1 aria-describedby="results-label"><?php echo __('Showing %1% results', array('%1%' => $pager->getNbResults())) ?></h1>
      <span class="sub" id="results-label"><?php echo __('Clipboard') ?></span>
    </div>
  <?php end_slot() ?>

  <?php slot('before-content') ?>
    <section class="browse-options">
      <?php echo get_partial('default/printPreviewButton', array('class' => 'clipboard-print')) ?>

      <div class="pickers">
        <?php echo get_partial('default/genericPicker', array(
          'options' => $uiLabels,
          'label' => __('Entity type'),
          'param' => 'type')) ?>

        <?php if ($pager->getNbResults()): ?>
          <?php echo get_partial('default/sortPickers', array('options' => $sortOptions)) ?>
        <?php endif; ?>
      </div>
    </section>
  <?php end_slot() ?>

  <?php slot('content') ?>
    <div id="content">
      <?php if (!isset($pager) || !$pager->getNbResults()): ?>
        <div class="text-section">
          <?php echo __('No results for this entity type.') ?>
        </div>
      <?php endif; ?>

      <?php foreach ($pager->getResults() as $hit): ?>
        <?php if ('QubitInformationObject' === $entityType): ?>
          <?php echo get_partial('search/searchResult', array('hit' => $hit, 'culture' => $selectedCulture)) ?>
        <?php elseif ('QubitActor' === $entityType): ?>
          <?php echo get_partial('actor/searchResult', array('doc' => $hit->getData(), 'culture' => $selectedCulture, 'clipboardType' => 'actor')) ?>
        <?php elseif ('QubitRepository' === $entityType): ?>
          <?php echo get_partial('actor/searchResult', array('doc' => $hit->getData(), 'culture' => $selectedCulture, 'clipboardType' => 'repository')) ?>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>

    <?php echo get_partial('default/pager', array('pager' => $pager)) ?>

    <?php if (isset($pager) && $pager->getNbResults()): ?>
      <section class="actions">
        <ul>
          <li><button class="c-btn c-btn-delete" id="clipboard-clear" data-clipboard-type="<?php echo $type ?>"><?php echo __('Clear %1 clipboard', array('%1' => lcfirst($uiLabels[$type]))) ?></button></li>
          <li><?php echo link_to(__('Save'), array('module' => 'clipboard', 'action' => 'save'), array('class' => 'c-btn', 'id' => 'clipboard-save')) ?></li>
          <li><?php echo link_to(__('Export'), array('module' => 'clipboard', 'action' => 'export', 'type' => $type), array('class' => 'c-btn')) ?></li>
          <?php if (sfConfig::get('app_clipboard_send_enabled', false) && !empty(sfConfig::get('app_clipboard_send_url', ''))): ?>
            <li>
              <button class="c-btn"
                      id="clipboard-send"
                      data-url="<?php echo sfConfig::get('app_clipboard_send_url') ?>"
                      data-method="<?php echo sfConfig::get('app_clipboard_send_http_method', 'POST') ?>"
                      data-message="<?php echo sfConfig::get('app_clipboard_send_message_html', __('Sending...')) ?>"
                      data-site-base-url="<?php echo sfConfig::get('app_siteBaseUrl') ?>"
                      data-empty-message="<?php echo __('No items in clipboard to send.') ?>">
                <?php echo sfConfig::get('app_clipboard_send_button_text', __('Send')) ?>
              </button>
            </li>
          <?php endif; ?>
        </ul>
      </section>
    <?php endif; ?>
  <?php end_slot() ?>
<?php endif; ?>
