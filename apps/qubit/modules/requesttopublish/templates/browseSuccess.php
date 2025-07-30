<?php use_helper('Date') ?>

<h1><?php echo __('Browse Request To Publish Items %1%', array('%1%' => sfConfig::get('app_ui_label_requesttopublish'))) ?></h1>

<div>
  <ul class="nav nav-tabs" id="job-tabs">
    <li<?php if ('all' === $filter) { ?> class="active"<?php } ?>><?php echo link_to(__('All Request to publish'), ['filter' => 'all'] + $sf_data->getRaw('sf_request')->getParameterHolder()->getAll()); ?></li>
    <li<?php if ('pending' === $filter) { ?> class="active"<?php } ?>><?php echo link_to(__('In review'), ['filter' => 'pending'] + $sf_data->getRaw('sf_request')->getParameterHolder()->getAll()); ?></li>
    <li<?php if ('rejected' === $filter) { ?> class="active"<?php } ?>><?php echo link_to(__('Rejected'), ['filter' => 'rejected'] + $sf_data->getRaw('sf_request')->getParameterHolder()->getAll()); ?></li>
    <li<?php if ('approved' === $filter) { ?> class="active"<?php } ?>><?php echo link_to(__('Approved'), ['filter' => 'approved'] + $sf_data->getRaw('sf_request')->getParameterHolder()->getAll()); ?></li>
  </ul>
</div>

<table class="table table-bordered sticky-enabled"> 
  <thead>
    <tr>
	  <th>
        <?php echo __('Status') ?>
      </th>
      <th class="sortable">
        <?php echo __('Archival Description') ?>
      </th>
	  <th>
        <?php echo __('Action') ?>
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
        <?php echo __('Institution') ?>
      </th>
	  <th>
        <?php echo __('Planned use') ?>
      </th>
	  <th>
        <?php echo __('Motivation') ?>
      </th>
	  <th>
        <?php echo __('Need image by') ?>
      </th>
	  <th>
        <?php echo __('Created date') ?>
      </th>
	  <th>
        <?php echo __('Completed date') ?>
      </th>
    </tr>
  </thead><tbody>
    <?php foreach ($pager->getResults() as $item): ?>
      <tr class="<?php echo 0 == @++$row % 2 ? 'even' : 'odd' ?>">
		<td>
		     <?php if ($item->statusId == QubitTerm::IN_REVIEW_ID) { ?>
				<?php echo "In review"; ?>
			 <?php } else if ($item->statusId == QubitTerm::REJECTED_ID) {?>
				<?php echo "Rejected"; ?>
			 <?php } else {
				 echo "Approved";
					} ?>
		</td>
        <td>
			<?php $informationObjectsRequestToPublish = QubitInformationObject::getById($item->object_id); ?>
			<?php if (isset($informationObjectsRequestToPublish->identifier)) { ?> <?php echo link_to($informationObjectsRequestToPublish, array($informationObjectsRequestToPublish, 'module' => 'informationobject')) ?> <?php } ?>
        </td>
        <td>
			<?php echo link_to(render_title('Action'), array($item, 'module' => 'requesttopublish', 'action' => 'editRequestToPublish')) ?>
        </td>
        <td>
			<?php echo $item->rtp_name ?>
        </td>
		<td>
			<?php echo $item->rtp_surname; ?>
        </td>
		<td>
			<?php echo $item->rtp_phone; ?>
        </td>
		<td>
			<?php echo $item->rtp_email; ?>
		</td>
		<td>
			<?php echo $item->rtp_institution; ?>
		</td>
		<td>
			<?php echo $item->rtp_planned_use; ?>
		</td>
		<td>
			<?php echo $item->rtp_motivation; ?>
		</td>
		<td>
			<?php echo $item->rtp_need_image_by; ?>
		</td>
		<td>
          <?php echo $item->createdAt ?>
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

