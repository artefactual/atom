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
  <h1 class="do-print"><?php echo $this->i18n->__('Box labels'); ?></h1>

  <h1 class="label">
    <?php echo $resource->getTitle(['cultureFallback' => true]); ?>
  </h1>

  <table class="sticky-enabled">
    <thead>
      <tr>
        <th>
          <?php echo $this->i18n->__('#'); ?>
        </th><th>
          <?php echo $this->i18n->__('Reference code'); ?>
        </th><th>
          <?php echo $this->i18n->__('Physical object name'); ?>
        </th><th>
          <?php echo $this->i18n->__('Title'); ?>
        </th><th>
          <?php echo $this->i18n->__('Creation date(s)'); ?>
        </th>
      </tr>
    </thead><tbody>
      <?php $row = 1;
      foreach ($results as $item) { ?>
        <tr>
          <td>
            <?php echo $row++; ?>
          </td><td>
            <?php echo render_value_inline($item['referenceCode']); ?>
          </td><td>
            <?php echo render_value_inline($item['physicalObjectName']); ?>
          </td><td>
            <?php echo render_value_inline($item['title']); ?>
          </td><td>
            <?php if ($item['creationDates']) { ?>
              <ul>
                <?php foreach (explode('|', $item['creationDates']) as $creationDate) { ?>
                  <li><?php echo render_value_inline($creationDate); ?></li>
                <?php } ?>
              </ul>
            <?php } ?>
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
