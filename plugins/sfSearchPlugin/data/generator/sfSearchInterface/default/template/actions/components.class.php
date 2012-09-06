[?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * <?php echo $this->getGeneratedModuleName() ?> components
 *
 * @package ##PROJECT_NAME##
 * @subpackage <?php echo $this->getGeneratedModuleName() ?>

 * @author Carl Vondrick
 */
abstract class <?php echo $this->getGeneratedModuleName() ?>Components extends sfComponents
{
  public function executeShowResult()
  {
    sfLoader::loadHelpers('Partial');

    switch ($this->result->getServiceName())
    {
      <?php
      $config = $this->get('simple.services', array());
      if (!isset($config['default']))
      {
        $config['default'] = array();
      }

      foreach ($config as $name => $properties)
      {
        if ($name == 'default')
        {
          echo "default:\n";
        }
        else
        {
          echo "case '$name':\n";
        }

        $partial = $this->get('simple.services.' . $name . '.partial', null);
        if ($partial != null)
        {
          echo "\$this->mode        = 'partial' ;\n";
          echo "\$this->partial     = '$partial';\n";
        }
        else
        {
          echo "\$this->mode        = 'result';\n";
          echo "\$this->title       = \$this->result->" . $this->get('simple.services.' . $name . '.title',       $this->get('simple.services.default.title',       'getTitle'      )) . "();\n";
          echo "\$this->description = \$this->result->" . $this->get('simple.services.' . $name . '.description', $this->get('simple.services.default.description', 'getDescription')) . "();\n";
          echo "\$this->route       = \$this->result->" . $this->get('simple.services.' . $name . '.route',       $this->get('simple.services.default.route',       'getRoute'      )) . "();\n";
        }

        echo "break;\n";
      }
      ?>
    }
  }
}
