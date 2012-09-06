[?php if ($pager->haveToPaginate()): ?]
  <div class="pager">
    [?php if (!$pager->atFirstPage()): ?]
      <a href="[?php echo url_for('<?php echo $this->moduleName ?>/search') ?][?php echo $pager->getPageUrl($pager->getPreviousPage()) ?]">Prev</a>
    [?php endif ?]

    [?php foreach ($pager->getLinks(<?php echo $this->get('simple.results.pager_links', 6) ?>) as $link): ?]
      [?php if ($link == $pager->getPage()): ?]
        <strong>[?php echo $link ?]</strong>
      [?php else: ?]
        <a href="[?php echo url_for('<?php echo $this->moduleName ?>/search') ?][?php echo $pager->getPageUrl($link) ?]">[?php echo $link ?]</a>
      [?php endif ?]
    [?php endforeach ?]

    [?php if (!$pager->atLastPage()): ?]
      <a href="[?php echo url_for('<?php echo $this->moduleName ?>/search') ?][?php echo $pager->getPageUrl($pager->getNextPage()) ?]">Next</a>
    [?php endif ?]
  </div>
[?php endif ?]
