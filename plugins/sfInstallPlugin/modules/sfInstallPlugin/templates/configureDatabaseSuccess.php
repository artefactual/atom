<h2>Database configuration</h2>

<?php if (count($database) > 0): ?>

  <h3>The following errors must be resolved before you can continue the installation process:</h3>

  <div class="messages error">
    <ul>
      <?php foreach ($database as $e): ?>
        <li><?php echo $e->getMessage() ?></li>
      <?php endforeach; ?>
    </ul>
  </div>

<?php endif; ?>

<?php slot('before-content') ?>
  <?php echo $form->renderFormTag(url_for(array('module' => 'sfInstallPlugin', 'action' => 'configureDatabase'))) ?>
<?php end_slot() ?>

  <fieldset class="collapsible">

    <legend>Basic options</legend>

    <div class="description">
      <p>
        To set up your database, enter the following information.
      </p>
    </div>

    <?php echo $form->databaseName->help('The name of the <em>MySQL</em> database your data will be stored in.  It must exist on your server before '.$sf_response->getTitle().' can be installed.')->renderRow() ?>

    <?php echo $form->databaseUsername->renderRow() ?>

    <?php echo $form->databasePassword->renderRow() ?>

  </fieldset>

  <fieldset class="collapsible collapsed">

    <legend>Advanced options</legend>

    <div class="description">
      <p>
        These options are only necessary for some sites.  If you're not sure what you should enter here, leave the default settings or check with your hosting provider.
      </p>
    </div>

    <?php echo $form->databaseHost->help('If your database is located on a different server, change this.')->renderRow() ?>

    <?php echo $form->databasePort->help('If your database server is listening on a non-standard port, enter its number.')->renderRow() ?>

  </fieldset>

<?php slot('after-content') ?>
  <section class="actions">
    <ul>
      <li><input class="c-btn c-btn-submit" type="submit" value="Save and continue"/></li>
    </ul>
  </section>
  </form>
<?php end_slot() ?>
