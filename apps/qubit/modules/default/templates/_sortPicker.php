<div id="sort-header">

  <div class="sort-options">

    <label><?php echo __('Sort by:') ?></label>

    <div class="dropdown">

      <div class="dropdown-selected">

        <?php if (isset($sf_request->sort) && isset($options[$sf_request->sort])): ?>
          <span><?php echo $options[$sf_request->sort] ?></span>
          <?php unset($options[$sf_request->sort]) ?>
        <?php else: ?>
          <span><?php echo array_shift($options) ?></span>
        <?php endif; ?>

      </div>

      <ul class="dropdown-options">

        <span class="pointer"></span>

        <?php foreach ($options as $key => $value): ?>
          <li>
            <?php $urlParams = array(
              'module' => $sf_request->module,
              'action' => $sf_request->action,
              'sort' => $key) + $sf_request->getParameterHolder()->getAll() ?>
            <a href="<?php echo url_for($urlParams) ?>" data-order="<?php echo $key ?>">
              <span><?php echo $value ?></span>
            </a>
          </li>
        <?php endforeach; ?>

      </ul>

    </div>

  </div>

</div>
