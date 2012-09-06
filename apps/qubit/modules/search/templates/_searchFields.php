<?php $count = 0 ?>
<?php if (isset($sf_request->searchFields)): ?>

  <?php foreach ($sf_request->searchFields as $key => $item): ?>
    <?php if (empty($item['query'])) continue ?>
    <tr>
      <td>
        <select name="searchFields[<?php echo $key ?>][operator]">
          <option value="and"<?php echo $item['operator'] == 'and' ? ' selected="selected"' : '' ?>><?php echo __('and') ?></option>
          <option value="or"<?php echo $item['operator'] == 'or' ? ' selected="selected"' : '' ?>><?php echo __('or') ?></option>
          <option value="not"<?php echo $item['operator'] == 'not' ? ' selected="selected"' : '' ?>><?php echo __('not') ?></option>
        </select>
      </td><td>
        <input type="text" name="searchFields[<?php echo $key ?>][query]" value="<?php echo esc_entities($item['query']) ?>"/>
      </td><td><?php echo __('in') ?>&nbsp;
        <select style="width: 90%;" name="searchFields[<?php echo $key ?>][field]">
          <option value=""<?php echo $item['field'] == '' ? ' selected="selected"' : '' ?>><?php echo __('Any field') ?></option>
          <option value="title"<?php echo $item['field'] == 'title' ? ' selected="selected"' : '' ?>><?php echo __('Title') ?></option>
          <option value="creatorHistory"<?php echo $item['field'] == 'creatorHistory' ? ' selected="selected"' : '' ?>><?php echo __('Admin/biographical history') ?></option>
          <option value="archivalHistory"<?php echo $item['field'] == 'archivalHistory' ? ' selected="selected"' : '' ?>><?php echo __('Archival history') ?></option>
          <option value="scopeAndContent"<?php echo $item['field'] == 'scopeAndContent' ? ' selected="selected"' : '' ?>><?php echo __('Scope and content') ?></option>
          <option value="extentAndMedium"<?php echo $item['field'] == 'extentAndMedium' ? ' selected="selected"' : '' ?>><?php echo __('Extent and medium') ?></option>
          <option value="subject"<?php echo $item['field'] == 'subject' ? ' selected="selected"' : '' ?>><?php echo __('Subject access points') ?></option>
          <option value="name"<?php echo $item['field'] == 'name' ? ' selected="selected"' : '' ?>><?php echo __('Name access points') ?></option>
          <option value="place"<?php echo $item['field'] == 'place' ? ' selected="selected"' : '' ?>><?php echo __('Place access points') ?></option>
          <option value="identifier"<?php echo $item['field'] == 'identifier' ? ' selected="selected"' : '' ?>><?php echo __('Identifier') ?></option>
        </select>
      </td><td><?php echo __('using') ?>&nbsp;
        <select name="searchFields[<?php echo $key ?>][match]" style="width: 100px;">
          <option value="keyword"<?php echo $item['match'] == 'keyword' ? ' selected="selected"' : '' ?>><?php echo __('keyword') ?></option>
          <option value="phrase"<?php echo $item['match'] == 'phrase' ? ' selected="selected"' : '' ?>><?php echo __('phrase') ?></option>
        </select>
      </td>
    </tr>
    <?php $count++ ?>
  <?php endforeach; ?>
<?php endif; ?>

<tr>
  <td>
    <select name="searchFields[<?php echo $count ?>][operator]">
      <option value="and"><?php echo __('and') ?></option>
      <option value="or"><?php echo __('or') ?></option>
      <option value="not"><?php echo __('not') ?></option>
    </select>
  </td><td>
    <input type="text" name="searchFields[<?php echo $count ?>][query]"/>
  </td><td><?php echo __('in') ?>&nbsp;
    <select style="width: 90%;" name="searchFields[<?php echo $count ?>][field]">
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
  </td><td><?php echo __('using') ?>&nbsp;
    <select name="searchFields[<?php echo $count ?>][match]" style="width: 100px;">
      <option value="keyword"><?php echo __('keyword') ?></option>
      <option value="phrase"><?php echo __('phrase') ?></option>
    </select>
  </td>
</tr>
