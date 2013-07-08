<?php $sf_response->addJavaScript('date') ?>

<div class="section">

  <h3><?php echo __('Date(s)') ?></h3>

  <table class="multiRow">
    <thead>
      <tr>
        <th style="width: 40%">
          <?php echo __('Date') ?>
        </th><th style="width: 30%">
          <?php echo __('Start') ?>
        </th><th style="width: 30%">
          <?php echo __('End') ?>
        </th>
      </tr>
    </thead><tbody>

      <?php $i = 0; foreach ($resource->getDates() as $item): ?>

        <?php $form->getWidgetSchema()->setNameFormat("editDates[$i][%s]") ?>

        <tr class="<?php echo 0 == $i % 2 ? 'even' : 'odd' ?> related_obj_<?php echo $item->id ?> date">
          <td>
            <div class="animateNicely">
              <input type="hidden" name="editDates[<?php echo $i ?>][id]" value="<?php echo $item->id ?>"/>
              <?php echo $form->getWidgetSchema()->renderField('date', $item->getDate(array('cultureFallback' => true))) ?>
            </div>
          </td><td>
            <div class="animateNicely">
              <?php echo $form->getWidgetSchema()->renderField('startDate', Qubit::renderDate($item->startDate)) ?>
            </div>
          </td><td>
            <div class="animateNicely">
              <?php echo $form->getWidgetSchema()->renderField('endDate', Qubit::renderDate($item->endDate)) ?>
            </div>
          </td>
        </tr>

        <?php $i++ ?>
      <?php endforeach; ?>

      <?php $form->getWidgetSchema()->setNameFormat("editDates[$i][%s]") ?>

      <tr class="<?php echo 0 == $i % 2 ? 'even' : 'odd' ?> date">
        <td>
          <div class="animateNicely">
            <?php echo $form->date ?>
          </div>
        </td><td>
          <div class="animateNicely">
            <?php echo $form->startDate ?>
          </div>
        </td><td>
          <div class="animateNicely">
            <?php echo $form->endDate ?>
          </div>
        </td>
      </tr>

    </tbody>
  </table>

  <div class="description">
    <?php echo __('Identify and record the date(s) of the unit of description. Record as a single date or a range of dates as appropriate. Use YYYY-MM-DD format for the <em>Date</em> field. The <em>End date</em> field can be used to indicate a date range. The <em>Date display</em> field can be used to enter free-text date information.') ?>
  </div>

</div>
