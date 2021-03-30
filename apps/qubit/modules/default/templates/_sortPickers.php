<?php echo get_partial('default/genericPicker', [
    'options' => $sf_data->getRaw('options'),
    'param' => 'sort',
    'label' => __('Sort by'), ]); ?>

<?php echo get_partial('default/genericPicker', [
    'options' => ['asc' => __('Ascending'), 'desc' => __('Descending')],
    'param' => 'sortDir',
    'label' => __('Direction'), ]); ?>
