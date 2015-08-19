<div class="section">

  <h3><?php echo __('Name(s)') ?></h3>

  <table class="table table-bordered multiRow">
    <thead>
      <tr>
        <th style="width: 60%">
          <?php echo __('Actor name') ?>
        </th><th style="width: 40%">
          <?php echo __('Type') ?>
        </th>
      </tr>
    </thead><tbody>

      <?php $i = 0; foreach ($resource->getActorEvents() as $item): ?>

        <?php if (isset($item->actor)): ?>

          <?php $form->getWidgetSchema()->setNameFormat("editNames[$i][%s]") ?>

          <tr class="<?php echo 0 == $i % 2 ? 'even' : 'odd' ?> related_obj_<?php echo $item->id ?>">
            <td>
              <div class="animateNicely">
                <input type="hidden" name="editNames[<?php echo $i ?>][id]" value="<?php echo $item->id ?>"/>
                <?php echo render_title($item->actor) ?>
              </div>
            </td><td>
              <div class="animateNicely">
                <?php echo $item->getType(array('cultureFallback' => true)) ?>
              </div>
            </td>
          </tr>

          <?php $i++ ?>
        <?php endif; ?>
      <?php endforeach; ?>

      <?php $form->getWidgetSchema()->setNameFormat("editNames[$i][%s]") ?>

      <tr class="<?php echo 0 == $i % 2 ? 'even' : 'odd' ?>">
        <td>
          <div class="animateNicely">
            <?php echo $form->actor->render(array('class' => 'form-autocomplete')) ?>

            <?php if (QubitAcl::check(QubitActor::getRoot(), 'create')): ?>
              <input class="add" type="hidden" data-link-existing="true" value="<?php echo url_for(array('module' => 'actor', 'action' => 'add')) ?> #authorizedFormOfName"/>
            <?php endif; ?>

            <input class="list" type="hidden" value="<?php echo url_for(array('module' => 'actor', 'action' => 'autocomplete')) ?>"/>
          </div>
        </td><td>
          <div class="animateNicely">
            <?php echo $form->type ?>
          </div>
        </td>
      </tr>

    </tbody>
  </table>

  <div class="description">
    <?php echo __('Identify and record the name(s) and type(s) of the unit of description.') ?>
  </div>

</div>
