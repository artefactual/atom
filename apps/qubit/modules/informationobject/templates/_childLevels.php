<div class="section">

  <h3><?php echo __('Add new child levels') ?></h3>

  <table class="table table-bordered multiRow">
    <thead>
      <tr>
        <th style="width: 20%">
          <?php echo __('Identifier') ?>
        </th><th style="width: 20%">
          <?php echo __('Level') ?>
        </th><th style="width: 60%">
          <?php echo __('Title') ?>
        </th>
      </tr>
    </thead><tbody>
      <tr>
        <td>
          <input type="text" name="updateChildLevels[0][identifier]"/>
        </td><td>
          <select name="updateChildLevels[0][levelOfDescription]" id="updateChildLevels_0_levelOfDescription">
            <option value="">&nbsp;</option>
            <?php foreach (QubitTerm::getLevelsOfDescription() as $item): ?>
              <option value="<?php echo $item->id ?>"><?php echo $item->__toString() ?></option>
            <?php endforeach; ?>
          </select>
        </td><td>
          <input type="text" name="updateChildLevels[0][title]"/>
        </td>
      </tr>
    </tbody>
  </table>

  <?php if (isset($help)): ?>
    <div class="description">
      <?php echo $help ?>
    </div>
  <?php endif ?>

</div>
