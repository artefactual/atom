<section id="advanced-search-fields">

  <p><?php echo __('Narrow down your search results.') ?></p>

  <?php $count = 0 ?>

  <?php if (isset($sf_request->s)): ?>

    <?php foreach ($sf_request->s as $key => $item): ?>

      <?php if (empty($item['q'])) continue ?>

      <div class="criteria">

        <div class="boolean">
          <select name="s[<?php echo $count ?>][o]">
            <option value="and"<?php echo $item['o'] == 'and' ? ' selected="selected"' : '' ?>><?php echo __('and') ?></option>
            <option value="or"<?php echo $item['o'] == 'or' ? ' selected="selected"' : '' ?>><?php echo __('or') ?></option>
            <option value="not"<?php echo $item['o'] == 'not' ? ' selected="selected"' : '' ?>><?php echo __('not') ?></option>
          </select>
        </div>

        <div class="criterion">
          <input type="text" placeholder="<?php echo __('Search') ?>" name="s[<?php echo $key ?>][q]" value="<?php echo esc_entities($item['q']) ?>"/>
          <div class="in">
            <span><?php echo __('in') ?></span>
            <select name="s[<?php echo $key ?>][f]">
              <option value=""<?php echo $item['f'] == '' ? ' selected="selected"' : '' ?>><?php echo __('Any field') ?></option>
              <option value="title"<?php echo $item['f'] == 'title' ? ' selected="selected"' : '' ?>><?php echo __('Title') ?></option>
              <option value="creatorHistory"<?php echo $item['f'] == 'creatorHistory' ? ' selected="selected"' : '' ?>><?php echo __('Admin/biographical history') ?></option>
              <option value="archivalHistory"<?php echo $item['f'] == 'archivalHistory' ? ' selected="selected"' : '' ?>><?php echo __('Archival history') ?></option>
              <option value="scopeAndContent"<?php echo $item['f'] == 'scopeAndContent' ? ' selected="selected"' : '' ?>><?php echo __('Scope and content') ?></option>
              <option value="extentAndMedium"<?php echo $item['f'] == 'extentAndMedium' ? ' selected="selected"' : '' ?>><?php echo __('Extent and medium') ?></option>
              <option value="subject"<?php echo $item['f'] == 'subject' ? ' selected="selected"' : '' ?>><?php echo __('Subject access points') ?></option>
              <option value="name"<?php echo $item['f'] == 'name' ? ' selected="selected"' : '' ?>><?php echo __('Name access points') ?></option>
              <option value="place"<?php echo $item['f'] == 'place' ? ' selected="selected"' : '' ?>><?php echo __('Place access points') ?></option>
              <option value="identifier"<?php echo $item['f'] == 'identifier' ? ' selected="selected"' : '' ?>><?php echo __('Identifier') ?></option>
            </select>
          </div>
        </div>

      </div>

      <?php $count++ ?>

    <?php endforeach; ?>

  <?php endif; ?>

  <div class="criteria">

    <div class="boolean">
      <select name="s[<?php echo $count ?>][o]">
        <option value="and"><?php echo __('and') ?></option>
        <option value="or"><?php echo __('or') ?></option>
        <option value="not"><?php echo __('not') ?></option>
      </select>
    </div>

    <div class="criterion">
      <input type="text" placeholder="<?php echo __('Search') ?>" name="s[<?php echo $count?>][q]"/>
      <div class="in">
        <span><?php echo __('in') ?></span>
        <select name="s[<?php echo $count ?>][f]">
          <option value=""><?php echo __('Any field') ?></option>
          <option value="title"><?php echo __('Title') ?></option>
          <option value="creatorHistory"><?php echo __('Admin/biographical history') ?></option>
          <option value="archivalHistory"><?php echo __('Archival history') ?></option>
          <option value="scopeAndContent"><?php echo __('Scope and content') ?></option>
          <option value="extentAndMedium"><?php echo __('Extent and medium') ?></option>
          <option value="subject"><?php echo __('Subject access points') ?></option>
          <option value="name"><?php echo __('Name access points') ?></option>
          <option value="place"><?php echo __('Place access points') ?></option>
          <option value="identifier"><?php echo __('Identifier') ?></option>
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
