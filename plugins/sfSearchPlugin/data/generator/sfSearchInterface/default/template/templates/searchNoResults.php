[?php use_helper('I18N') ?]
<h2>[?php echo __('<?php echo $this->get('mode.simple.no_results.heading', 'No Search Results') ?>') ?]</h2>

<p>[?php echo __('<?php echo $this->get('mode.simple.no_results.error', 'Sorry, but no results matched your query.  Try different terms.') ?>') ?]</p>

[?php include_partial('simpleControls', array('form' => $form)) ?]
