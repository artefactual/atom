<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Select menu of US states.
 * 
 * Here's an example usage:
 * 
 *    $this->setWidgets(array(
 *      'state' => new sfWidgetFormSelectUSState(array('add_empty' => 'Select a state...')),
 *    ));
 * 
 *    $this->setValidators(array(
 *      'state' => sfValidatorChoice(array('choices' => sfWidgetFormSelectUSState::getStateAbbreviations())),
 *    ));
 * 
 * @package    sfFormExtraPlugin
 * @subpackage widget
 * @author     Kris Wallsmith <kris.wallsmith@symfony-project.com>
 * @version    SVN: $Id: sfWidgetFormSelectUSState.class.php 15371 2009-02-09 19:19:20Z Kris.Wallsmith $
 */
class sfWidgetFormSelectUSState extends sfWidgetFormSelect
{
  /**
   * @see sfWidget
   */
  public function __construct($options = array(), $attributes = array())
  {
    $options['choices'] = new sfCallable(array($this, 'getChoices'));

    parent::__construct($options, $attributes);
  }

  /**
   * @see sfWidget
   */
  protected function configure($options = array(), $attributes = array())
  {
    $this->addOption('add_empty', false);

    parent::configure($options, $attributes);
  }

  /**
   * Returns choices for the current widget.
   * 
   * @return array
   */
  public function getChoices()
  {
    $choices = array();
    if (false !== $this->getOption('add_empty'))
    {
      $choices[''] = true === $this->getOption('add_empty') ? '' : $this->getOption('add_empty');
    }

    $choices = array_merge($choices, self::getStates());

    return $choices;
  }

  /**
   * Returns an associative array of US states.
   * 
   * @return array
   */
  static public function getStates()
  {
    return self::$states;
  }

  /**
   * Sets the array of states.
   * 
   * @param array $states
   */
  static public function setStates(array $states)
  {
    self::$states = $states;
  }

  /**
   * Returns an array of state abbreviations.
   * 
   * @return array
   */
  static public function getStateAbbreviations()
  {
    return array_keys(self::$states);
  }

  static protected $states = array(
    'AL' => 'Alabama',
    'AK' => 'Alaska',
    'AZ' => 'Arizona',
    'AR' => 'Arkansas',
    'CA' => 'California',
    'CO' => 'Colorado',
    'CT' => 'Connecticut',
    'DE' => 'Delaware',
    'DC' => 'District Of Columbia',
    'FL' => 'Florida',
    'GA' => 'Georgia',
    'HI' => 'Hawaii',
    'ID' => 'Idaho',
    'IL' => 'Illinois',
    'IN' => 'Indiana',
    'IA' => 'Iowa',
    'KS' => 'Kansas',
    'KY' => 'Kentucky',
    'LA' => 'Louisiana',
    'ME' => 'Maine',
    'MD' => 'Maryland',
    'MA' => 'Massachusetts',
    'MI' => 'Michigan',
    'MN' => 'Minnesota',
    'MS' => 'Mississippi',
    'MO' => 'Missouri',
    'MT' => 'Montana',
    'NE' => 'Nebraska',
    'NV' => 'Nevada',
    'NH' => 'New Hampshire',
    'NJ' => 'New Jersey',
    'NM' => 'New Mexico',
    'NY' => 'New York',
    'NC' => 'North Carolina',
    'ND' => 'North Dakota',
    'OH' => 'Ohio',
    'OK' => 'Oklahoma',
    'OR' => 'Oregon',
    'PA' => 'Pennsylvania',
    'RI' => 'Rhode Island',
    'SC' => 'South Carolina',
    'SD' => 'South Dakota',
    'TN' => 'Tennessee',
    'TX' => 'Texas',
    'UT' => 'Utah',
    'VT' => 'Vermont',
    'VA' => 'Virginia',
    'WA' => 'Washington',
    'WV' => 'West Virginia',
    'WI' => 'Wisconsin',
    'WY' => 'Wyoming',
  );
}
