<?php
    $options = $sf_data->getRaw('options');
    $param = $sf_data->getRaw('param');
    if (isset($sf_request->{$param}, $options[$sf_request->{$param}])) {
        $active = $sf_request->{$param};
    } else {
        $active = array_key_first($options);
    }
?>

<div class="dropdown d-inline-block">
  <button class="btn btn-sm atom-btn-white dropdown-toggle text-wrap" type="button" id="<?php echo $param; ?>-button" data-bs-toggle="dropdown" aria-expanded="false">
    <?php echo $sf_data->getRaw('label').': '.$options[$active]; ?>
  </button>
  <ul class="dropdown-menu dropdown-menu-end mt-2" aria-labelledby="<?php echo $param; ?>-button">
    <?php foreach ($options as $key => $value) { ?>
      <li>
        <a
          href="<?php echo url_for(
              ['module' => $sf_request->module, 'action' => $sf_request->action, $param => $key]
              + $sf_data->getRaw('sf_request')->getParameterHolder()->getAll()
          ); ?>"
          class="dropdown-item<?php echo $active == $key ? ' active' : ''; ?>">
          <?php echo $value; ?>
        </a>
      </li>
    <?php } ?>
  </ul>
</div>
