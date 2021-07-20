<div class="section">

  <table class="table table-bordered" id="contactInformations">
    <caption>
      <?php echo __('Related contact information'); ?>
    </caption><thead>
      <tr>
        <th style="width: 60%">
          <?php echo __('Contact person'); ?>
        </th><th style="width: 15%">
          <?php echo __('Primary'); ?>
        </th><th style="text-align: center; width: 10%">
        </th>
      </tr>
    </thead><tbody>
      <?php foreach ($resource->contactInformations as $item) { ?>
        <tr class="<?php echo 0 == @++$row % 2 ? 'even' : 'odd'; ?> related_obj_<?php echo $item->id; ?>" id="<?php echo url_for([$item, 'module' => 'contactinformation']); ?>">
          <td>
            <?php echo render_title($item->contactPerson); ?>
          </td><td>
            <input type="checkbox"<?php echo $item->primaryContact ? ' checked="checked"' : ''; ?> disabled="disabled" />
          </td><td style="text-align: center">
            <input class="multiDelete" name="deleteContactInformations[]" type="checkbox" value="<?php echo $item->id; ?>"/>
          </td>
        </tr>
      <?php } ?>
    </tbody>
  </table>

  <!-- NOTE dialog.js wraps this *entire* table in a YUI dialog
  <div class="section" id="contactInformationRelation">

    <h3><?php echo __('Related contact information'); ?></h3>

    <div id="contactInformationRelationTabView" class="yui-navset">

      <ul class="yui-nav">

        <li class="selected"><a href="#contactInformationRelation_Tab1"><em><?php echo __('Main'); ?></em></a></li>
        <li><a href="#contactInformationRelation_Tab2"><em><?php echo __('Physical location'); ?></em></a></li>
        <li><a href="#contactInformationRelation_Tab3"><em><?php echo __('Other details'); ?></em></a></li>

      </ul>

      <div class="yui-content">

        <div id="contactInformationRelation_Tab1">

          <?php echo $form->primaryContact
              ->label(__('Primary contact'))
              ->renderRow(); ?>

          <?php echo $form->contactPerson
              ->label(__('Contact person'))
              ->renderRow(); ?>

          <?php echo $form->telephone
              ->label(__('Phone'))
              ->renderRow(); ?>

          <?php echo $form->fax
              ->label(__('Fax'))
              ->renderRow(); ?>

          <?php echo $form->email
              ->label(__('Email'))
              ->renderRow(); ?>

          <?php echo $form->website
              ->label(__('URL'))
              ->renderRow(); ?>

        </div>

        <div id="contactInformationRelation_Tab2">

          <?php echo $form->streetAddress
              ->label(__('Street address'))
              ->renderRow(); ?>

          <?php echo $form->region
              ->label(__('Region/province'))
              ->renderRow(); ?>

          <?php echo $form->countryCode
              ->label(__('Country'))
              ->renderRow(); ?>

          <?php echo $form->postalCode
              ->label(__('Postal code'))
              ->renderRow(); ?>

          <?php echo $form->city
              ->label(__('City'))
              ->renderRow(); ?>

          <?php echo $form->latitude
              ->label(__('Latitude'))
              ->renderRow(); ?>

          <?php echo $form->longitude
              ->label(__('Longitude'))
              ->renderRow(); ?>

        </div>

        <div id="contactInformationRelation_Tab3">

          <?php echo $form->contactType
              ->label(__('Contact type'))
              ->renderRow(); ?>

          <?php echo $form->note
              ->label(__('Note'))
              ->renderRow(); ?>

        </div>

     </div>

  </div>
  -->

</div>
