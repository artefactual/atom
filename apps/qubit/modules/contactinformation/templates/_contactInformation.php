<section class="contact-info">
  <?php if (!empty($contactInformation->contactPerson)) { ?>
    <div class="field <?php echo render_b5_show_field_css_classes(); ?>">
      <?php echo render_b5_show_label(''); ?>
      <div class="agent <?php echo render_b5_show_value_css_classes(); ?>">
        <span class="text-primary">
          <?php echo render_value_inline($contactInformation->contactPerson); ?>
        </span>
        <?php if ($contactInformation->primaryContact) { ?>
          <span class="primary-contact">
            <?php echo __('Primary contact'); ?>
          </span>
        <?php } ?>
      </div>
    </div>
  <?php } ?>

  <?php echo render_show(__('Type'), render_value_inline($contactInformation->getContactType(['cultureFallback' => true])), ['valueClass' => 'type']); ?>

  <div class="field adr <?php echo render_b5_show_field_css_classes(); ?>">
    <?php echo render_b5_show_label(__('Address')); ?>
    <div class="<?php echo render_b5_show_value_css_classes(); ?>">

      <?php echo render_show(__('Street address'), render_value_inline($contactInformation->streetAddress), ['isSubField' => true]); ?>

      <?php echo render_show(__('Locality'), render_value_inline($contactInformation->getCity(['cultureFallback' => true])), ['isSubField' => true]); ?>

      <?php echo render_show(__('Region'), render_value_inline($contactInformation->getRegion(['cultureFallback' => true])), ['isSubField' => true]); ?>

      <?php echo render_show(__('Country name'), format_country($contactInformation->countryCode), ['isSubField' => true]); ?>

      <?php echo render_show(__('Postal code'), render_value_inline($contactInformation->postalCode), ['isSubField' => true]); ?>

    </div>

  </div>

  <?php echo render_show(__('Telephone'), render_value_inline($contactInformation->telephone), ['valueClass' => 'tel']); ?>

  <?php echo render_show(__('Fax'), render_value_inline($contactInformation->fax), ['valueClass' => 'fax']); ?>

  <?php echo render_show(__('Email'), render_value_inline($contactInformation->email), ['valueClass' => 'email']); ?>

  <?php echo render_show(__('URL'), render_value_inline($contactInformation->website), ['valueClass' => 'url']); ?>

  <?php echo render_show(__('Note'), render_value($contactInformation->getNote(['cultureFallback' => true])), ['valueClass' => 'note']); ?>
</section>
