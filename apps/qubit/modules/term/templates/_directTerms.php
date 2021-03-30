<?php if (!isset($sf_request->onlyDirect) && isset($aggs['direct']) && 0 < $aggs['direct']['doc_count']) { ?>
  <div class="search-result media-summary">
    <p>
      <?php echo __('%1% results directly related', [
          '%1%' => $aggs['direct']['doc_count'], ]); ?>
      <?php $params = $sf_data->getRaw('sf_request')->getGetParameters(); ?>
      <?php unset($params['page']); ?>
      <a href="<?php echo url_for([$resource, 'module' => 'term'] + $params + ['onlyDirect' => true]); ?>">
        <i class="fa fa-search"></i>
        <?php echo __('Exclude narrower terms'); ?>
      </a>
    </p>
  </div>
<?php } ?>
