<?php use_helper('Date') ?>

<h1><?php echo __('Browse Feedback Items %1%', array('%1%' => sfConfig::get('app_ui_label_feedback'))) ?></h1>

<div>
  <ul class="nav nav-tabs" id="job-tabs">
    <li<?php if ('all' === $filter) { ?> class="active"<?php } ?>><?php echo link_to(__('All feedback'), ['filter' => 'all'] + $sf_data->getRaw('sf_request')->getParameterHolder()->getAll()); ?></li>
    <li<?php if ('pending' === $filter) { ?> class="active"<?php } ?>><?php echo link_to(__('Pending'), ['filter' => 'pending'] + $sf_data->getRaw('sf_request')->getParameterHolder()->getAll()); ?></li>
    <li<?php if ('completed' === $filter) { ?> class="active"<?php } ?>><?php echo link_to(__('Completed'), ['filter' => 'completed'] + $sf_data->getRaw('sf_request')->getParameterHolder()->getAll()); ?></li>
  </ul>
</div>

<table class="table table-bordered sticky-enabled"> 
  <thead>
    <tr>
      <th class="sortable">
        <?php echo __('Archival Description') ?>
      </th>
      <th class="sortable">
        <?php echo __('Feedback Description') ?>
      </th>
	  <th>
        <?php echo __('Remarks') ?>
      </th>
	  <th>
        <?php echo __('Name') ?>
      </th>
	  <th>
        <?php echo __('Surname') ?>
      </th>
	  <th>
        <?php echo __('Phone number') ?>
      </th>
	  <th>
        <?php echo __('e-Mail address') ?>
      </th>
	  <th>
        <?php echo __('Relationship') ?>
      </th>
	  <th>
        <?php echo __('Feedback Type') ?>
      </th>
	  <th>
        <?php echo __('Created date') ?>
      </th>
	  <th>
        <?php echo __('Status') ?>
      </th>
	  <th>
        <?php echo __('Completed date') ?>
      </th>
    </tr>
  </thead><tbody>
    <?php foreach ($pager->getResults() as $item): ?>
      <tr class="<?php echo 0 == @++$row % 2 ? 'even' : 'odd' ?>">
        <td>
			<?php $informationObjectsFeedback = QubitInformationObject::getById($item->object_id); ?>
			<?php if (isset($informationObjectsFeedback->identifier)) { ?> <?php echo link_to($informationObjectsFeedback, array($informationObjectsFeedback, 'module' => 'informationobject')) ?> <?php } ?>
        </td>
        <td>
			<?php echo link_to(render_title($item), array($item, 'module' => 'feedback', 'action' => 'editFeedback')) ?>
        </td>
		<td>
			<?php echo $item->remarks; ?> 
        </td>
		<td>
			<?php echo $item->feed_name; ?>
        </td>
		<td>
			<?php echo $item->feed_surname; ?>
        </td>
		<td>
			<?php echo $item->feed_phone; ?>
        </td>
		<td>
			<?php echo $item->feed_email; ?>
		</td>
		<td>
			<?php echo $item->feed_relationship; ?>
		</td>
		<td>
			<?php if ($item->feedTypeId == 0 ) {
					echo "General";
				} else if ($item->feedTypeId == 1 ) {
					echo "Error";
				} else if ($item->feedTypeId == 2 ) {
					echo "Suggestion";
				} else if ($item->feedTypeId == 3 ) {
					echo "Correction";
				} else if ($item->feedTypeId == 4 ) {
					echo "Need assistance";
				} else {
					echo "Unknown";
				}?>
		</td>
		<td>
          <?php echo $item->createdAt ?>
		</td>
		<td>
		     <?php if ($item->statusId == QubitTerm::PENDING_ID) { ?>
				<?php echo "Pending"; ?>
			 <?php } else {?>
				<?php echo "Completed"; ?>
			 <?php } ?>
		</td>
		<td>
          <?php echo $item->completedAt ?>
		</td>
	  </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php echo get_partial('default/pager', array('pager' => $pager)) ?>

 <section class="actions">
      <ul>
		<li><input class="c-btn c-btn-submit" type="button" onclick="history.back();" value="Back"></li>
      </ul>
  </section>

