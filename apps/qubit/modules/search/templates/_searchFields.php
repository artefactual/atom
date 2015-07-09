<section id="advanced-search-fields">

  <p><?php echo __('Narrow down your search results.') ?></p>

  <?php if (isset($criteria)): ?>

    <?php foreach ($criteria as $key => $item): ?>

      <div class="criteria">

        <div class="boolean">
          <select name="so<?php echo $key ?>">
            <option value="and"<?php echo $item['operator'] == 'and' ? ' selected="selected"' : '' ?>><?php echo __('and') ?></option>
            <option value="or"<?php echo $item['operator'] == 'or' ? ' selected="selected"' : '' ?>><?php echo __('or') ?></option>
            <option value="not"<?php echo $item['operator'] == 'not' ? ' selected="selected"' : '' ?>><?php echo __('not') ?></option>
          </select>
        </div>

        <div class="criterion">
          <input type="text" placeholder="<?php echo __('Search') ?>" name="sq<?php echo $key ?>" value="<?php echo esc_entities($item['query']) ?>"/>
          <div class="in">
            <span><?php echo __('in') ?></span>
            <select name="sf<?php echo $key ?>">
              <option value=""<?php echo $item['field'] == '' ? ' selected="selected"' : '' ?>><?php echo __('Any field') ?></option>
              <option value="title"<?php echo $item['field'] == 'title' ? ' selected="selected"' : '' ?>><?php echo __('Title') ?></option>
              <?php if (($template == 'rad' && check_field_visibility('app_element_visibility_rad_archival_history'))
                || ($template == 'isad' && check_field_visibility('app_element_visibility_isad_archival_history'))
                || ($template != 'isad' && $template != 'rad')): ?>
                <option value="archivalHistory"<?php echo $item['field'] == 'archivalHistory' ? ' selected="selected"' : '' ?>><?php echo __('Archival history') ?></option>
              <?php endif; ?>
              <option value="scopeAndContent"<?php echo $item['field'] == 'scopeAndContent' ? ' selected="selected"' : '' ?>><?php echo __('Scope and content') ?></option>
              <option value="extentAndMedium"<?php echo $item['field'] == 'extentAndMedium' ? ' selected="selected"' : '' ?>><?php echo __('Extent and medium') ?></option>
              <option value="subject"<?php echo $item['field'] == 'subject' ? ' selected="selected"' : '' ?>><?php echo __('Subject access points') ?></option>
              <option value="name"<?php echo $item['field'] == 'name' ? ' selected="selected"' : '' ?>><?php echo __('Name access points') ?></option>
              <option value="place"<?php echo $item['field'] == 'place' ? ' selected="selected"' : '' ?>><?php echo __('Place access points') ?></option>
              <option value="identifier"<?php echo $item['field'] == 'identifier' ? ' selected="selected"' : '' ?>><?php echo __('Identifier') ?></option>
              <option value="referenceCode"<?php echo $item['field'] == 'referenceCode' ? ' selected="selected"' : '' ?>><?php echo __('Reference code') ?></option>
            </select>
          </div>
        </div>

      </div>

    <?php endforeach; ?>

  <?php endif; ?>

  <div class="criteria">

    <?php $count = isset($key) ? $key++ : 0 ?>

    <div class="boolean">
      <select name="so<?php echo $count ?>">
        <option value="and"><?php echo __('and') ?></option>
        <option value="or"><?php echo __('or') ?></option>
        <option value="not"><?php echo __('not') ?></option>
      </select>
    </div>

    <div class="criterion">
      <input type="text" placeholder="<?php echo __('Search') ?>" name="sq<?php echo $count?>"/>
      <div class="in">
        <span><?php echo __('in') ?></span>
        <select name="sf<?php echo $count ?>">
          <option value=""><?php echo __('Any field') ?></option>
          <option value="title"><?php echo __('Title') ?></option>
          <?php if (($template == 'rad' && check_field_visibility('app_element_visibility_rad_archival_history'))
            || ($template == 'isad' && check_field_visibility('app_element_visibility_isad_archival_history'))
            || ($template != 'isad' && $template != 'rad')): ?>
            <option value="archivalHistory"><?php echo __('Archival history') ?></option>
          <?php endif; ?>
          <option value="scopeAndContent"><?php echo __('Scope and content') ?></option>
          <option value="extentAndMedium"><?php echo __('Extent and medium') ?></option>
          <option value="subject"><?php echo __('Subject access points') ?></option>
          <option value="name"><?php echo __('Name access points') ?></option>
          <option value="place"><?php echo __('Place access points') ?></option>
          <option value="identifier"><?php echo __('Identifier') ?></option>
          <option value="referenceCode"><?php echo __('Reference code') ?></option>
        </select>
      </div>
    </div>

  </div>

  <div id="add-new-criteria">
    <div class="btn-group">
      <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
        <?php echo __('Add new criteria') ?>
        <span class="caret"></span>
      </a>
      <ul class="dropdown-menu">
        <li><a href="#" id="add-criteria-and"><?php echo __('And') ?></a></li>
        <li><a href="#" id="add-criteria-or"><?php echo __('Or') ?></a></li>
        <li><a href="#" id="add-criteria-not"><?php echo __('Not') ?></a></li>
      </ul>
    </div>
  </div>

</section>
