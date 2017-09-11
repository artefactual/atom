<?php if (!isset($aggs[$name]) || (!isset($filters[$name]) && count($aggs[$name]) < 2)) return ?>
<?php $openned = (isset($sf_request->$name) || (isset($open) && $open && 0 < count($aggs[$name]))) ?>

<section class="facet <?php if ($openned) echo 'open' ?>">
  <div class="facet-header">
    <h3><a href="#" aria-expanded="<?php echo $openned ?>"><?php echo $label ?></a></h3>
  </div>

  <div class="facet-body" id="<?php echo $id ?>">
    <ul>

      <?php $filters = sfOutputEscaper::unescape($filters) ?>

      <?php if ($name !== 'languages'): ?>
        <li <?php if (!isset($filters[$name])) echo 'class="active"' ?>>
          <?php echo link_to(__('All'),
            array($name => null,'page' => null) +
            $sf_data->getRaw('sf_request')->getParameterHolder()->getAll(), array('title' => '')) ?>
        </li>
      <?php endif; ?>

      <?php foreach ($aggs[$name] as $bucket): ?>
        <?php $active = ((isset($filters[$name]) && $filters[$name] == $bucket['key']) ||
          (!isset($filters[$name]) && $bucket['key'] == 'unique_language')) ?>

        <li <?php if ($active) echo 'class="active"' ?>>
          <?php echo link_to(__($bucket['display']) . '<span>, ' . $bucket['doc_count'] . ' ' . __('results') . '</span>',
            array('page' => null, $name => $bucket['key'] == 'unique_language' ? null : $bucket['key']) +
            $sf_data->getRaw('sf_request')->getParameterHolder()->getAll(), array('title' => '')) ?>
          <span class="facet-count" aria-hidden="true"><?php echo $bucket['doc_count'] ?></span>
        </li>
      <?php endforeach; ?>

    </ul>
  </div>
</section>
