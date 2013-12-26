<div class="inline-search">

  <form method="get" action="<?php echo $route ?>">

    <div class="input-prepend input-append">

      <?php if (isset($fields)): ?>
        <?php if (isset($sf_request->subqueryField) && 0 < strlen($sf_request->subqueryField)): ?>
          <input type="hidden" name="subqueryField" id="subqueryField" value="<?php echo $sf_request->subqueryField ?>" />
        <?php else: ?>
          <input type="hidden" name="subqueryField" id="subqueryField" value="<?php echo $fields[0] ?>" />
        <?php endif; ?>

        <div class="btn-group">
          <button class="btn dropdown-toggle" data-toggle="dropdown">
            <?php if (isset($sf_request->subqueryField) && 0 < strlen($sf_request->subqueryField)): ?>
              <?php echo $sf_request->subqueryField ?>
            <?php else: ?>
              <?php echo $fields[0] ?>
            <?php endif; ?>
            <span class="caret"></span>
          </button>
          <ul class="dropdown-menu">
            <?php foreach ($fields as $field): ?>
              <li><a href="#"><?php echo $field ?></a></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <?php if (isset($sf_request->subquery)): ?>
        <input type="text" name="subquery" value="<?php echo esc_entities($sf_request->subquery) ?>" />
        <a class="btn" href="<?php echo $cleanRoute ?>">
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
