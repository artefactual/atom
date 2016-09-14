<html>
<head>
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
  <h1 class="do-print"><?php echo $this->i18n->__('Box labels') ?></h1>

  <h1 class="label">
    <?php echo $resource->getTitle(array('cultureFallback' => true)) ?>
  </h1>

  <table class="sticky-enabled">
    <thead>
      <tr>
        <th>
          <?php echo $this->i18n->__('#') ?>
        </th><th>
          <?php echo $this->i18n->__('Reference code') ?>
        </th><th>
          <?php echo $this->i18n->__('Physical object name') ?>
        </th><th>
          <?php echo $this->i18n->__('Title') ?>
        </th><th>
          <?php echo $this->i18n->__('Creation date(s)') ?>
        </th>
      </tr>
    </thead><tbody>
      <?php $row = 1; foreach ($results as $item): ?>
        <tr>
          <td>
            <?php echo $row++ ?>
          </td><td>
            <?php echo $item['referenceCode'] ?>
          </td><td>
            <?php echo $item['physicalObjectName'] ?>
          </td><td>
            <?php echo $item['title'] ?>
          </td><td>
            <?php if ($item['creationDates']): ?>
              <ul>
                <?php foreach (explode('|', $item['creationDates']) as $creationDate): ?>
                  <li><?php echo $creationDate ?></li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <div id="result-count">
    <?php echo $this->i18n->__('Showing %1% results', array('%1%' => count($results))) ?>
  </div>
</body>
</html>
