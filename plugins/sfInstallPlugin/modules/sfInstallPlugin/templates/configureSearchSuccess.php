<h2>Search configuration</h2>

<?php if (count($errors) > 0): ?>

  <h3>The following errors must be resolved before you can continue the installation process:</h3>

  <div class="messages error">
    <ul>
      <?php foreach ($errors as $item): ?>
        <li><?php echo $item ?></li>
      <?php endforeach; ?>
    </ul>
  </div>

<?php endif; ?>

<?php slot('before-content') ?>
  <?php echo $form->renderFormTag(url_for(array('module' => 'sfInstallPlugin', 'action' => 'configureSearch'))) ?>
<?php end_slot() ?>

  <fieldset class="collapsible">

    <legend>Basic options</legend>

    <div class="description">
      <p>
        To set up the search server, enter the following information.
      </p>
    </div>

    <?php echo $form
      ->searchHost
      ->renderRow() ?>

    <?php echo $form
      ->searchPort
      ->renderRow() ?>

    <?php echo $form
      ->searchIndex
      ->help('The name of the <em>ElasticSearch</em> index your data will be stored in.')
      ->renderRow() ?>

  </fieldset>

<?php slot('after-content') ?>
  <section class="actions">
    <ul>
      <li><input class="c-btn c-btn-submit" type="submit" value="Save and continue"/></li>
    </ul>
  </section>
  </form>
<?php end_slot() ?>
