<section id="no-search-results">

  <i class="icon-search"></i>

  <p class="no-results-found">

    <?php echo __('No results found.') ?>

    <?php if (isset($suggestion)): ?>
      <span class="suggestion">
        <?php echo __('Did you mean %1%?', array(
          '%1%' => link_to($suggestion['text'],
            array('module' => 'search', 'query' => esc_entities($suggestion['text']))))) ?>
      </span>
    <?php endif; ?>

  </p>

</section>
