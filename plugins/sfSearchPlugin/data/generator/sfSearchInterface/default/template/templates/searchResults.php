[?php use_helper('I18N') ?]
<h2>[?php echo __('<?php echo $this->get('modes.simple.results.heading', 'Search Results') ?>') ?]</h2>

<ol start="[?php echo $pager->getStartPosition() ?]">
[?php foreach ($pager->getResults() as $result): ?]
  <li>[?php include_component($this->getModuleName(), 'showResult', array('result' => $result)) ?]</li>
[?php endforeach ?]
</ol>

[?php include_partial('pager', array('pager' => $pager)) ?]

[?php include_partial('simpleControls', array('form' => $form)) ?]
