<section id="no-search-results">

  <i class="icon-search"></i>

  <p class="no-results-found">

    <?php echo __('No results found.') ?>

    <?php if (isset($suggestion)): ?>
      <?php $params = $sf_data->getRaw('sf_params')->getAll() ?>
      <?php $params['query'] = $suggestion['text'] ?>
      <span class="suggestion">
        <?php echo __('Did you mean %1%?', array('%1%' => link_to($suggestion['text'], $params))) ?>
      </span>
    <?php endif; ?>

  </p>

</section>
