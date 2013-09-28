<section id="no-search-results">

  <i class="icon-search"></i>

  <p class="no-results-found">

    <?php echo __('No results found.') ?>

    <?php if (isset($suggestion)): ?>
      <?php $sf_params->set('query', esc_entities($suggestion['text'])) ?>
      <span class="suggestion">
        <?php echo __('Did you mean %1%?', array(
          '%1%' => link_to($suggestion['text'],
            $sf_params->getAll()))) ?>
      </span>
    <?php endif; ?>

  </p>

</section>
