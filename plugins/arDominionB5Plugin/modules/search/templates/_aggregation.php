<?php if (!isset($aggs[$name]) || (!isset($filters[$name]) && (count($aggs[$name]) < 2 || ('languages' == $name && count($aggs[$name]) < 3)))) { ?>
  <?php return; ?>
<?php } ?>

<?php $openned = (isset($sf_request->{$name}) || (isset($open) && $open && 0 < count($aggs[$name]))); ?>

<div class="accordion mb-3">
  <div class="accordion-item aggregation">
    <h2 class="accordion-header" id="heading-<?php echo $name; ?>">
      <button
        class="accordion-button<?php echo $openned ? '' : ' collapsed'; ?>"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#collapse-<?php echo $name; ?>"
        aria-expanded="<?php echo $openned ? 'true' : 'false'; ?>"
        aria-controls="collapse-<?php echo $name; ?>">
        <?php echo $label; ?>
      </button>
    </h2>
    <div
      id="collapse-<?php echo $name; ?>"
      class="accordion-collapse collapse<?php echo $openned ? ' show' : ''; ?> list-group list-group-flush"
      aria-labelledby="heading-<?php echo $name; ?>">
      <?php $filters = sfOutputEscaper::unescape($filters); ?>

      <?php if ('languages' !== $name) { ?>
        <?php echo link_to(
            __('All'),
            [$name => null, 'page' => null] + $sf_data->getRaw('sf_request')->getParameterHolder()->getAll(),
            ['class' => 'list-group-item list-group-item-action d-flex justify-content-between align-items-center'.(!isset($filters[$name]) ? ' active text-decoration-underline' : '')]
        ); ?>
      <?php } ?>
    
      <?php foreach ($aggs[$name] as $bucket) { ?>
        <?php $active = ((isset($filters[$name]) && $filters[$name] == $bucket['key'])
            || (!isset($filters[$name]) && 'unique_language' == $bucket['key'])); ?>

        <?php echo link_to(
            __(strip_markdown($bucket['display']))
            .'<span class="visually-hidden">, '
            .$bucket['doc_count'].' '.__('results')
            .'</span>'
            .'<span aria-hidden="true" class="ms-3 text-nowrap">'
            .$bucket['doc_count']
            .'</span>',
            ['page' => null, $name => 'unique_language' == $bucket['key'] ? null : $bucket['key']] + $sf_data->getRaw('sf_request')->getParameterHolder()->getAll(),
            ['class' => 'list-group-item list-group-item-action d-flex justify-content-between align-items-center text-break'.($active ? ' active text-decoration-underline' : '')]
        ); ?>
      <?php } ?>
    </div>
  </div>
</div>
