<?php if (!isset($aggs[$name]) || (!isset($filters[$name]) && (count($aggs[$name]) < 2 || ('languages' == $name && count($aggs[$name]) < 3)))) { ?>
  <?php return; ?>
<?php } ?>

<?php $openned = (isset($sf_request->{$name}) || (isset($open) && $open && 0 < count($aggs[$name]))); ?>

<section class="facet <?php echo $openned ? 'open' : ''; ?>">
  <div class="facet-header">
    <h3><a href="#" aria-expanded="<?php echo $openned; ?>"><?php echo $label; ?></a></h3>
  </div>

  <div class="facet-body" id="<?php echo $id; ?>">
    <ul>

      <?php $filters = sfOutputEscaper::unescape($filters); ?>

      <?php if ('languages' !== $name) { ?>
        <li <?php echo !isset($filters[$name]) ? 'class="active"' : ''; ?>>
          <?php echo link_to(__('All'),
            [$name => null, 'page' => null] +
            $sf_data->getRaw('sf_request')->getParameterHolder()->getAll(), ['title' => __('All')]); ?>
        </li>
      <?php } ?>

      <?php foreach ($aggs[$name] as $bucket) { ?>
        <?php $active = ((isset($filters[$name]) && $filters[$name] == $bucket['key'])
          || (!isset($filters[$name]) && 'unique_language' == $bucket['key'])); ?>

        <li <?php echo $active ? 'class="active"' : ''; ?>>
          <?php echo link_to(__(strip_markdown($bucket['display'])).'<span>, '.$bucket['doc_count'].' '.__('results').'</span>',
            ['page' => null, $name => 'unique_language' == $bucket['key'] ? null : $bucket['key']] +
            $sf_data->getRaw('sf_request')->getParameterHolder()->getAll(), ['title' => __(strip_markdown($bucket['display']))]); ?>
          <span class="facet-count" aria-hidden="true"><?php echo $bucket['doc_count']; ?></span>
        </li>
      <?php } ?>

    </ul>
  </div>
</section>
