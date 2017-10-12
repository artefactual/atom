<?php

/*
 * This file is part of the Access to Memory (AtoM) software.
 *
 * Access to Memory (AtoM) is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Access to Memory (AtoM) is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Access to Memory (AtoM).  If not, see <http://www.gnu.org/licenses/>.
 */

class arMarcXmlParser extends QubitSaxParser
{
  protected $dispatcher;
  protected $formatter;
  protected $taxonomy;
  protected $termCounter;
  protected $termData;
  protected $currentTagAttr;
  protected $currentLabel;

  public function __construct($dispatcher, $formatter, $taxonomy)
  {
    parent::__construct();

    $this->dispatcher = $dispatcher;
    $this->formatter = $formatter;
    $this->taxonomy = $taxonomy;
    $this->termCounter = 0;
  }

  /*
   * Tags functions
   */

  protected function marc_collectionTagInit()
  {
    $this->log(sprintf('Starting collection import to "%s" taxonomy:', $this->taxonomy));
  }

  protected function marc_recordTagInit()
  {
    // Initiate term data
    $this->termData = array(
      'fastIdentifier' => null,
      'lang' => null,
      'prefLabel' => '',
      'altLabels' => array(),
      'broaderTerms' => array(),
      'relatedTerms' => array()
    );
  }

  protected function marc_datafieldTagInit()
  {
    // Get current tag, needed to determine termData
    // field in the subfield tag function bellow
    $this->currentTagAttr = $this->attr('tag');

    // Initiate current label, used to determine its
    // type and value from different subfield elements
    $this->currentLabel = array(
      'type' => null,
      'value' => ''
    );
  }

  protected function marc_subfieldTag()
  {
    // A tag attribute from the datafield is required
    if (!isset($this->currentTagAttr))
    {
      return;
    }

    // Data is only needed from the following code attributes
    $codeAttr = $this->attr('code');
    if (!isset($codeAttr) || !in_array($codeAttr, array('a', 'b', 'c', 'p', 'x', 'w', 'z', '0')))
    {
      return;
    }

    // Do not import empty subfield elements
    $data = trim($this->data());
    if (strlen($data) === 0)
    {
      return;
    }

    // Add data to termData based on the datafield tag attribute
    switch ($this->currentTagAttr)
    {

      case '016':
        // FAST identifier in elements with code="a" attribute
        if ($codeAttr === 'a' && substr($data, 0, 3) === 'fst')
        {
          $this->termData['fastIdentifier'] = ltrim(substr($data, 3), 0);
        }

        break;

      case '040':
        // Language in elements with code="b" attribute
        if ($codeAttr === 'b')
        {
          $this->termData['lang'] = $data;
        }

        break;

      // Labels can be a concatenation of elements,
      // more info in the processLabel function.
      // Label types by tag attribute:
      //   - Preferred label: 150
      //   - Alternative labels: 410, 430, 450
      //   - Broader/Related terms labels: 500, 510, 530, 550, 551, 555
      case '150':
        $this->processLabel('prefLabel', $data, $codeAttr);

        break;

      case '410':
      case '430':
      case '450':
        $this->processLabel('altLabel', $data, $codeAttr);

        break;

      // Defaults to related term, changes to
      // broader in processLabel when needed
      case '500':
      case '510':
      case '530':
      case '550':
      case '551':
      case '555':
        $this->processLabel('relatedTerms', $data, $codeAttr);

        break;
    }
  }

  protected function marc_datafieldTag()
  {
    // Save current label to term data if needed
    if (!isset($this->currentLabel['type']) || strlen($this->currentLabel['value']) == 0)
    {
      return;
    }

    if ($this->currentLabel['type'] === 'prefLabel')
    {
      $this->termData['prefLabel'] = $this->currentLabel['value'];
    }
    else if ($this->currentLabel['type'] === 'altLabel')
    {
      $this->termData['altLabels'][] = $this->currentLabel['value'];
    }
    else
    {
      $term = array(
        'prefLabel' => $this->currentLabel['value'],
        'fastIdentifier' => $this->currentLabel['fastIdentifier'] ?
          $this->currentLabel['fastIdentifier'] :
          null
      );

      $this->termData[$this->currentLabel['type']][] = $term;
    }
  }

  protected function marc_recordTag()
  {
    $this->log('Creating: ' . $this->termData['prefLabel']);

    /*
    if ($this->termData['prefLabel'] == 'Galleria delle statue (Museo Pio-Clementino, Vatican City)')
    {
      $this->log(json_encode($this->termData, JSON_PRETTY_PRINT));
    }

    $this->term = new QubitTerm();
    $contHeadIdent = 'http://id.worldcat.org/fast/' . $this->termData['fastIdentifier'];
    $this->term->save();
    */

    $this->termCounter++;
  }

  protected function marc_collectionTag()
  {
    $this->log(sprintf('Collection import finished, %d terms have been imported.', $this->termCounter));
  }

  /*
   * Helper functions
   */

  public function log($messages)
  {
    if (!is_array($messages))
    {
      $messages = array($messages);
    }

    $this->dispatcher->notify(new sfEvent($this, 'command.log', $messages));
  }

  protected function processLabel($type, $data, $code)
  {
    // Set current label type if it's not already set
    if (!isset($this->currentLabel['type']))
    {
      $this->currentLabel['type'] = $type;
    }

    // Based on the subfield code attr
    switch ($code)
    {
      // Initiate label value
      case 'a':
        $this->currentLabel['value'] = $data;

        break;

      // Concatenate to label value with space
      case 'b':
      case 'c':
      case 'p':
        $this->currentLabel['value'] .= ' ' . $data;

        break;

      // Concatenate to label value with two hyphens
      case 'x':
      case 'z':
        $this->currentLabel['value'] .= '--' . $data;

        break;

      // Change type to broader instead of related
      case 'w':
        if ($data === 'g')
        {
          $this->currentLabel['type'] = 'broaderTerms';
        }

        break;

      // Add FAST identifier to related/broader terms
      case '0':
        $pos = strpos($data, 'fst');
        if ($pos !== false)
        {
          $this->currentLabel['fastIdentifier'] = ltrim(substr($data, $pos + 3), '0');
        }

        break;
    }
  }
}
