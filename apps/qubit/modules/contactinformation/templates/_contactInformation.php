<div class="field">

  <div class="vcard">

    <h3>
      <?php echo __('Contact information') ?>
      <span class="type">
        <?php echo $contactInformation->getContactType(array('cultureFallback' => true)) ?>
        <?php if ($contactInformation->primaryContact): ?>(<?php echo __('Primary contact') ?>)<?php endif; ?>
      </span>
    </h3>

    <?php if (!empty($contactInformation->contactPerson)): ?>
      <div class="agent">
        <?php echo render_value($contactPerson) ?>
      </div>
    <?php endif; ?>

    <div class="field adr">

      <h3><?php echo __('Address') ?></h3>

      <div>

        <div class="field">
          <h3><?php echo __('Street address') ?></h3>
          <div class="street-address">
            <?php echo render_value($contactInformation->streetAddress) ?>
          </div>
        </div>

        <div class="field">
          <h3><?php echo __('Locality') ?></h3>
          <div class="locality">
            <?php echo render_value($contactInformation->getCity(array('cultureFallback' => true))) ?>
          </div>
        </div>

        <div class="field">
          <h3><?php echo __('Region') ?></h3>
          <div class="region">
            <?php echo render_value($contactInformation->getRegion(array('cultureFallback' => true))) ?>
          </div>
        </div>

        <div class="field">
          <h3><?php echo __('Country name') ?></h3>
          <div class="country-name">
            <?php echo format_country($contactInformation->countryCode) ?>
          </div>
        </div>

        <div class="field">
          <h3><?php echo __('Postal code') ?></h3>
          <div class="postal-code">
            <?php echo render_value($contactInformation->postalCode) ?>
          </div>
        </div>

      </div>

    </div>

    <div class="field">
      <h3><?php echo __('Telephone') ?></h3>
      <div class="tel">
        <?php echo render_value($contactInformation->telephone) ?>
      </div>
    </div>

    <div class="field">
      <h3 class="type"><?php echo __('Fax') ?></h3>
      <div class="fax">
        <?php echo render_value($contactInformation->fax) ?>
      </div>
    </div>

    <div class="field">
      <h3><?php echo __('Email') ?></h3>
      <div class="email">
        <?php echo render_value($contactInformation->email) ?>
      </div>
    </div>

    <div class="field">
      <h3><?php echo __('URL') ?></h3>
      <div class="url">
        <?php echo render_value($contactInformation->website) ?>
      </div>
    </div>

    <div class="note">
      <?php echo render_value($contactInformation->getNote(array('cultureFallback' => true))) ?>
    </div>

  </div>

</div>
