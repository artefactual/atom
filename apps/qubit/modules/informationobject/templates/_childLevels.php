<div class="section">

  <h3><?php echo __('Add new child levels') ?></h3>

  <table class="table table-bordered multiRow" id="childsTable">
    <thead>
      <tr>
        <th style="width: 15%">
          <?php echo __('Identifier') ?>
        </th><th style="width: 15%">
          <?php echo __('Level') ?>
        </th><th style="width: 50%">
          <?php echo __('Title') ?>
        </th><th style="width: 20%">
          <?php echo __('Date') ?>
        </th>
      </tr>
    </thead><tbody>
      <tr class="date">
        <td>
          <input type="text" id="updateChildLevels_0_identifier" name="updateChildLevels[0][identifier]"/>
        </td><td>
          <select name="updateChildLevels[0][levelOfDescription]" id="updateChildLevels_0_levelOfDescription">
            <option value="">&nbsp;</option>
            <?php foreach (QubitTerm::getLevelsOfDescription() as $item): ?>
              <option value="<?php echo $item->id ?>"><?php echo $item->__toString() ?></option>
            <?php endforeach; ?>
          </select>
        </td><td>
          <input type="text" id="updateChildLevels_0_title" name="updateChildLevels[0][title]"/>
        </td><td>
          <input type="text" id="updateChildLevels_0_date" name="updateChildLevels[0][date]"/>
          <input type="hidden" id="updateChildLevels_0_startDate" name="updateChildLevels[0][startDate]"/>
          <input type="hidden" id="updateChildLevels_0_endDate" name="updateChildLevels[0][endDate]"/>
        </td>
      </tr>
    </tbody>
  </table>

  <?php if (isset($help)): ?>
    <div class="description">
      <?php echo $sf_data->getRaw('help') ?>
    </div>
  <?php endif ?>

</div>
