<?php $resource = $resources[$hit->object_id] ?>

<tr>
  <td>
    <?php echo $rank ?>
  </td>
  <td>
    <?php echo $resource->referenceCode ?>
  </td>
  <td>
    <p class="title"><?php echo link_to($resource->title, array('slug' => $resource->slug)) ?></p>
  </td>
  <td>
    <?php if ($hit->bot_parent_id >  QubitInformationObject::ROOT_ID): ?>
      <?php $parent = $parents[($hit->object_id)] ?>

      <?php if (!empty($parent->identifier)): ?>
        <?php $linkText = sprintf('%s (%s)', $parent->title, $parent->identifier) ?>
      <?php else: ?>
        <?php $linkText = $parent->title ?>
      <?php endif ?>

      <p class="title"><?php echo link_to($linkText, array('slug' => $parent->slug)) ?></p>
    <?php endif; ?>
  </td>
  <td>
    <?php echo $hit->visits ?>
  </td>
</tr>
