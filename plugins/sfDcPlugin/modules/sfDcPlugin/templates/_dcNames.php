<div class="section">

  <h3><?php echo __('Name(s)'); ?></h3>

  <table class="table table-bordered multiRow">
    <thead>
      <tr>
        <th style="width: 60%">
          <?php echo __('Actor name'); ?>
        </th><th style="width: 40%">
          <?php echo __('Type'); ?>
        </th>
      </tr>
    </thead><tbody>

      <?php $i = 0;
      foreach ($resource->getActorEvents() as $item) { ?>

        <?php if (isset($item->actor)) { ?>

          <?php $form->getWidgetSchema()->setNameFormat("editNames[{$i}][%s]"); ?>

          <tr class="<?php echo 0 == $i % 2 ? 'even' : 'odd'; ?> related_obj_<?php echo $item->id; ?>">
            <td>
              <div class="animateNicely">
                <input type="hidden" name="editNames[<?php echo $i; ?>][id]" value="<?php echo $item->id; ?>"/>
                <?php echo render_title($item->actor); ?>
              </div>
            </td><td>
              <div class="animateNicely">
                <?php echo render_value_inline($item->type); ?>
              </div>
            </td>
          </tr>

          <?php ++$i; ?>
        <?php } ?>
      <?php } ?>

      <?php $form->getWidgetSchema()->setNameFormat("editNames[{$i}][%s]"); ?>

      <tr class="<?php echo 0 == $i % 2 ? 'even' : 'odd'; ?>">
        <td>
          <div class="animateNicely">
            <?php echo $form->actor->render(['class' => 'form-autocomplete']); ?>

            <?php if (QubitAcl::check(QubitActor::getRoot(), 'create')) { ?>
              <input class="add" type="hidden" data-link-existing="true" value="<?php echo url_for(['module' => 'actor', 'action' => 'add']); ?> #authorizedFormOfName"/>
            <?php } ?>

            <input class="list" type="hidden" value="<?php echo url_for(['module' => 'actor', 'action' => 'autocomplete']); ?>"/>
          </div>
        </td><td>
          <div class="animateNicely">
            <?php echo $form->type; ?>
          </div>
        </td>
      </tr>

    </tbody>

    <tfoot>
      <tr>
        <td colspan="3"><a href="#" class="multiRowAddButton"><?php echo __('Add new'); ?></a></td>
      </tr>
    </tfoot>

  </table>

  <div class="description">
    <?php echo __('Identify and record the name(s) and type(s) of the unit of description.'); ?>
  </div>

</div>
