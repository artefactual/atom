  <section class="facet facet-date">
    <div class="facet-header">
      <p><?php echo __('Dates') ?></p>
    </div>
    <div class="facet-body" id="dates">
      <form name="dates" class="form" method="get" action="<?php echo url_for(array('module' => 'informationobject', 'action' => 'browse')) ?>">

        <?php if (
          (isset($sf_request->from) && ctype_digit($sf_request->from))
          || (isset($sf_request->to) && ctype_digit($sf_request->to))): ?>
          <a href="#" class="facet-dates-clear"><?php echo __('Clear') ?></a>
        <?php endif; ?>

        <ul>
          <li><label><?php echo __('From') ?></label></li>
          <?php if (isset($sf_request->from) && ctype_digit($sf_request->from)): ?>
            <li><input type="text" name="from" value="<?php echo $sf_request->from ?>" /></li>
          <?php else: ?>
            <li><input type="text" name="from" /></li>
          <?php endif; ?>
          <li><label><?php echo __('to') ?></label></li>
          <?php if (isset($sf_request->to) && ctype_digit($sf_request->to)): ?>
            <li><input type="text" name="to" value="<?php echo $sf_request->to ?>" /></li>
          <?php else: ?>
            <li><input type="text" name="to" /></li>
          <?php endif; ?>
          <li>
            <button type="submit" class="btn btn-small"><i class="fa fa-play-circle"></i></button>
          </li>
        </ul>

      </form>
    </div>
  </section>
