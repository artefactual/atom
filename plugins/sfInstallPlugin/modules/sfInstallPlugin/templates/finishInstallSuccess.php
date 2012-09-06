<h2>Installation finished</h2>

<p>
  Congratulations, <?php echo $sf_response->getTitle() ?> has been successfully installed.
</p>
<p>
  You may now visit <?php echo link_to('your new site', $sf_request->getRelativeUrlRoot().'/index.php') ?>.
</p>
