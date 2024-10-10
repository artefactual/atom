<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <style>
    table, thead {
      border-collapse: collapse;
      border: 1px solid black;
    }

    th, td {
      border: 1px solid #ddd;
      padding: 2px;
    }
  </style>
</head>

<body>
  <h1 class="do-print"><?php echo $this->i18n->__('Physical storage locations'); ?></h1>

  <h1 class="label">
    <?php echo render_title($resource); ?>
  </h1>

  <table class="sticky-enabled">
    <thead>
      <tr>
        <th>
          <?php echo $this->i18n->__('#'); ?>
        </th><th>
          <?php echo $this->i18n->__('Name'); ?>
        </th><th>
          <?php echo $this->i18n->__('Location'); ?>
        </th><th>
          <?php echo $this->i18n->__('Type'); ?>
        </th>
      </tr>
    </thead><tbody>
      <?php $row = 1;
      foreach ($results as $item) { ?>
        <tr>
          <td>
            <?php echo $row++; ?>
          </td><td>
            <?php echo link_to(render_title($item->getName(['cultureFallback' => true])), sfConfig::get('app_siteBaseUrl').'/'.$item->slug); ?>
          </td><td>
            <?php echo render_value_inline($item->getLocation(['cultureFallback' => true])); ?>
          </td><td>
            <?php echo render_value_inline($item->getType(['cultureFallback' => true])); ?>
          </td>
        </tr>
      <?php } ?>
    </tbody>
  </table>

  <div id="result-count">
    <?php echo $this->i18n->__('Showing %1% results', ['%1%' => count($results)]); ?>
  </div>
</body>
</html>
