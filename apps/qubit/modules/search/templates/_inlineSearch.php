<div class="inline-search">

  <form method="get" action="<?php echo $route; ?>">

    <?php if (isset($sf_request->subqueryField) && 0 < strlen($sf_request->subqueryField)) { ?>
      <input type="hidden" name="subqueryField" id="subqueryField" value="<?php echo $sf_request->subqueryField; ?>" />
    <?php } elseif (isset($fields)) { ?>
      <input type="hidden" name="subqueryField" id="subqueryField" value="<?php echo array_keys($sf_data->getRaw('fields'))[0]; ?>" />
    <?php } ?>

    <?php if (isset($sf_request->view)) { ?>
      <input type="hidden" name="view" value="<?php echo $sf_request->view; ?>"/>
    <?php } ?>

    <div class="input-prepend input-append">

      <?php if (isset($fields)) { ?>
        <div class="btn-group">
          <button class="btn dropdown-toggle" data-toggle="dropdown">
            <?php if (isset($sf_request->subqueryField) && 0 < strlen($sf_request->subqueryField)) { ?>
              <?php echo $sf_data->getRaw('fields')[$sf_request->subqueryField]; ?>
            <?php } else { ?>
              <?php echo array_values($sf_data->getRaw('fields'))[0]; ?>
            <?php } ?>
            <span class="caret"></span>
          </button>
          <ul class="dropdown-menu">
            <?php foreach ($fields as $value => $text) { ?>
              <li><a href="#" data-subquery-field-value="<?php echo $value; ?>"><?php echo $text; ?></a></li>
            <?php } ?>
          </ul>
        </div>
      <?php } ?>

      <?php if (isset($sf_request->subquery)) { ?>
        <input type="text" name="subquery" value="<?php echo $sf_request->subquery; ?>" />
        <a class="btn" href="<?php echo $cleanRoute; ?>">
          <i class="fa fa-times"></i>
        </a>
      <?php } else { ?>
        <input type="text" name="subquery" placeholder="<?php echo $label; ?>" />
      <?php } ?>

      <div class="btn-group">
        <button class="btn" type="submit">
          <i class="fa fa-search"></i>
        </button>
      </div>

    </div>

  </form>

</div>
