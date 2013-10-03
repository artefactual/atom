<div class="inline-search">

  <form method="get" action="<?php echo url_for(array('module' => $module, 'action' => $action)) ?>">

    <div class="input-append">
      <?php if (isset($sf_request->subquery)): ?>
        <input type="text" name="subquery" value="<?php echo esc_entities($sf_request->subquery) ?>" />
        <a class="btn" href="<?php echo url_for(array('module' => $module, 'action' => $action) + $params) ?>">
          <i class="icon-remove"></i>
        </a>
      <?php else: ?>
        <input type="text" name="subquery" placeholder="<?php echo $label ?>" />
      <?php endif; ?>
      <button class="btn" type="submit">
        <i class="icon-search"></i>
      </button>
    </div>

  </form>

</div>
