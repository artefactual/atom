<?php echo '<?' ?>xml version="1.0" encoding="utf-8" ?>
<OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">
  <responseDate><?php echo $date ?></responseDate>
  <request<?php echo $sf_data->getRaw('requestAttributes') ?>><?php echo $sf_data->getRaw('path') ?></request>
<?php if (!isset($sf_request->errorMsg)): ?>
  <error code="<?php echo $sf_request->errorCode ?>"/>
<?php else: ?>
  <error code="<?php echo $sf_request->errorCode ?>"><?php echo $sf_request->errorMsg ?></error>
<?php endif; ?>
</OAI-PMH>
