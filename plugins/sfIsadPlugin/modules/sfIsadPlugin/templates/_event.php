<?php $sf_response->addJavaScript('date') ?>
<?php $sf_response->addJavaScript('multiDelete') ?>

<div class="section">

  <h3><?php echo __('Date(s)') ?> <span class="form-required" title="<?php echo __('This is a mandatory element.') ?>">*</span></h3>

  <table>
    <thead>
      <tr>
        <th style="width: 25%">
          <?php echo __('Type') ?>
        </th><th style="width: 30%">
          <?php echo __('Date') ?>
        </th><th style="width: 20%">
          <?php echo __('Start') ?>
        </th><th style="width: 20%">
          <?php echo __('End') ?>
        </th><th style="width: 5%">
          <?php echo image_tag('delete', array('align' => 'top', 'class' => 'deleteIcon')) ?>
        </th>
      </tr>
    </thead><tbody>

      <?php $i = 0; foreach ($resource->getDates() as $item): ?>

        <?php $form->getWidgetSchema()->setNameFormat("editEvents[$i][%s]"); $i++ ?>

        <tr class="date <?php echo 0 == ++$i % 2 ? 'even' : 'odd' ?> related_obj_<?php echo $item->id ?>">
          <td>
            <div class="animateNicely">
              <input name="<?php echo $form->getWidgetSchema()->generateName('id') ?>" type="hidden" value="<?php echo url_for(array($item, 'module' => 'event')) ?>"/>
              <?php $save = $form->type->choices; $form->type->choices += array(url_for(array($item->type, 'module' => 'term')) => $item->type); echo $form->getWidgetSchema()->renderField('type', url_for(array($item->type, 'module' => 'term'))); $form->type->choices = $save ?>
            </div>
          </td><td>
            <div class="animateNicely">
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
          </td><td style="text-align: right">
            <div class="animateNicely">
              <input class="multiDelete" name="deleteEvents[]" type="checkbox" value="<?php echo url_for(array($item, 'module' => 'event')) ?>"/>
            </div>
          </td>
        </tr>

      <?php endforeach; ?>

      <?php $form->getWidgetSchema()->setNameFormat("editEvents[$i][%s]"); $i++ ?>

      <tr class="date <?php echo 0 == ++$i % 2 ? 'even' : 'odd' ?>">
        <td>
          <div class="animateNicely">
            <?php echo $form->type ?>
          </div>
        </td><td>
          <?php echo $form->date ?>
        </td><td>
          <?php echo $form->startDate ?>
        </td><td>
          <?php echo $form->endDate ?>
        </td><td>
          &nbsp;
        </td>
      </tr>

    </tbody>
  </table>

  <div class="description">
    <?php echo __('"Identify and record the date(s) of the unit of description. Identify the type of date given. Record as a single date or a range of dates as appropriate.” (ISAD 3.1.3). The Date display field can be used to enter free-text date information, including typographical marks to express approximation, uncertainty, or qualification. Use the start and end fields to make the dates searchable. Do not use any qualifiers or typographical symbols to express uncertainty. Acceptable date formats: YYYYMMDD, YYYY-MM-DD, YYYY-MM, YYYY.') ?>
  </div>

</div>
