<h3 class="fs-6 mb-2">
  <?php echo __('Add new child levels'); ?>
</h3>

<div class="table-responsive mb-2">
  <table class="table table-bordered mb-0 multi-row">
    <thead class="table-light">
      <tr>
        <th id="child-identifier-head" style="width: 20%">
          <?php echo __('Identifier'); ?>
        </th>
        <th id="child-level-head" style="width: 20%">
          <?php echo __('Level'); ?>
        </th>
        <th id="child-title-head" style="width: 40%">
          <?php echo __('Title'); ?>
        </th>
        <th id="child-date-head" style="width: 20%">
          <?php echo __('Date'); ?>
        </th>
        <th>
          <span class="visually-hidden"><?php echo __('Delete'); ?></span>
        </th>
      </tr>
    </thead>
    <tbody>
      <tr class="date">
        <td>
          <input
            type="text"
            name="updateChildLevels[0][identifier]"
            aria-labelledby="child-identifier-head"
            aria-describedby="child-table-help"
            class="form-control">
        </td>
        <td>
          <select
            name="updateChildLevels[0][levelOfDescription]"
            aria-labelledby="child-level-head"
            aria-describedby="child-table-help"
            class="form-control form-select">
            <option value=""></option>
            <?php foreach (QubitTerm::getLevelsOfDescription() as $item) { ?>
              <option value="<?php echo $item->id; ?>">
                <?php echo $item->__toString(); ?>
              </option>
            <?php } ?>
          </select>
        </td>
        <td>
          <input
            type="text"
            name="updateChildLevels[0][title]"
            aria-labelledby="child-title-head"
            aria-describedby="child-table-help"
            class="form-control">
        </td>
        <td>
          <input
            type="text"
            name="updateChildLevels[0][date]"
            id="updateChildLevels_0_date"
            aria-labelledby="child-date-head"
            aria-describedby="child-table-help"
            class="form-control">
          <input
            type="hidden"
            name="updateChildLevels[0][startDate]"
            id="updateChildLevels_0_startDate">
          <input
            type="hidden"
            name="updateChildLevels[0][endDate]"
            id="updateChildLevels_0_endDate">
        </td>
        <td>
          <button type="button" class="multi-row-delete btn atom-btn-white">
            <i class="fas fa-times" aria-hidden="true"></i>
            <span class="visually-hidden"><?php echo __('Delete row'); ?></span>
          </button>
        </td>
      </tr>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="5">
          <button type="button" class="multi-row-add btn atom-btn-white">
            <i class="fas fa-plus me-1" aria-hidden="true"></i>
            <?php echo __('Add new'); ?>
          </button>
        </td>
      </tr>
    </tfoot>
  </table>
</div>

<?php if (isset($help)) { ?>
  <div class="form-text mb-3" id="child-table-help">
    <?php echo $sf_data->getRaw('help'); ?>
  </div>
<?php } ?>
