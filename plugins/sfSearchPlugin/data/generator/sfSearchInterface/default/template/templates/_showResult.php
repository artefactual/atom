[?php if ($mode == 'partial'): ?]
  [?php include_partial($partial, array('result' => $result)) ?]
[?php elseif ($mode == 'result'): ?]
  <a href="[?php echo url_for($route) ?]">[?php echo $title ?]</a><br />
  [?php echo $description ?]
[?php endif ?]
