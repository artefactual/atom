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

/**
 * Check csv data
 *
 * @package    symfony
 * @subpackage task
 * @author     Mike Cantelon <mike@artefactual.com>
 */
class csvCheckImportTask extends csvImportBaseTask
{
    protected $namespace        = 'csv';
    protected $name             = 'check-import';
    protected $briefDescription = 'Check CSV data, providing diagnostic info';

    protected $detailedDescription = <<<EOF
Check CSV data, providing information about it
EOF;

  /**
   * @see sfTask
   */
  public function execute($arguments = array(), $options = array())
  {
    $this->validateOptions($options);

    $skipRows = ($options['skip-rows']) ? $options['skip-rows'] : 0;

    $filenames = explode(',', $arguments['filename']);

    $nonEmptyColumns    = array();
    $sampleColumnValues = array();
    $multiValueColumns  = array();
    $legacyIds          = array();
    $parentIds          = array();
    $rowCount           = 0;

    foreach($filenames as $filename)
    {

      if (false === $fh = fopen($filename, 'rb'))
      {
        throw new sfException('You must specify a valid filename');
      }

      // Get import definition
      $import = new QubitFlatfileImport(array(
        // Pass context
        'context' => sfContext::createInstance($this->configuration),

        'status' => array(
          'nonEmptyColumns'             => $nonEmptyColumns,
          'sampleColumnValues'          => $sampleColumnValues,
          'multiValueColumns'           => array(),
          'sampleOnlyMultivalueColumns' => false,
          'numberOfSampleValues'        => 1,
          'legacyIds'                   => $legacyIds,
          'parentIds'                   => $parentIds,
        ),

        'saveLogic' => function(&$self)
        {
          foreach($self->status['row'] as $key => $value)
          {
            $value = $self->status['row'][$key];
            $column = $self->columnNames[$key];

            $self->status['sampleColumnValues'][$column]
              = (isset($self->status['sampleColumnValues'][$column]))
                ? $self->status['sampleColumnValues'][$column]
                : array();

            // Check if column isn't empty
            if (trim($value))
            {
              $self->status['nonEmptyColumns'][$column] = true;

              if ($self->status['numberOfSampleValues'] > 0
                && (
                  count($self->status['sampleColumnValues'][$column])
                  < $self->status['numberOfSampleValues']
                )
                && (
                  !$self->status['sampleOnlyMultivalueColumns']
                    || substr_count($value, '|')
                )
              )
              {
                array_push($self->status['sampleColumnValues'][$column], trim($value));
              }
            }

            // Check for | character
            if (substr_count($value, '|'))
            {
              $self->status['multiValueColumns'][$column]
                = (isset($self->status['multiValueColumns'][$column]))
                  ? $self->status['multiValueColumns'][$column] + 1
                  : 1;
            }
          }

          // Store legacy IDs
          if ($self->columnExists('legacyId') && !empty($self->columnValue('legacyId')))
          {
            $self->status['legacyIds'][$self->columnValue('legacyId')] = true;
          }

          // Count parent ID uses
          if ($self->columnExists('parentId') && !empty($self->columnValue('parentId')))
          {
            $parentId = $self->columnValue('parentId');

            if (empty($self->status['parentIds'][$parentId]))
            {
              $self->status['parentIds'][$parentId] = 0;
            }

            $self->status['parentIds'][$parentId]++;
          }
        }
      ));

      $import->csv($fh, $skipRows);

      $legacyIds = $import->status['legacyIds'];
      $parentIds = $import->status['parentIds'];

      $nonEmptyColumns = array_merge(
        $nonEmptyColumns,
        $import->status['nonEmptyColumns']
      );

      // Add values of both arrays together
      $a = $multiValueColumns;
      $b = $import->status['multiValueColumns'];
      $c = array();

      // Add values of both arrays if possible
      foreach($a as $key => $value)
      {
        if (isset($b[$key]))
        {
          $c[$key] = $a[$key] + $b[$key];
        } else {
          $c[$key] = $a[$key];
        }
      }

      // Add values that only occur in array B
      foreach($b as $key => $value)
      {
        if (!isset($a[$key]))
        {
          $c[$key] = $value;
        }
      }

      $multiValueColumns = $c;

      $sampleColumnValues = $import->status['sampleColumnValues'];

      $rowCount = $rowCount + $import->status['rows'];
    }

    print "\nAnalysis complete.";

    print "\n\n". $rowCount ." rows, ". count($import->columnNames) ." columns.";

    if (count($import->columnNames != count($nonEmptyColumns)))
    {
      print "\n\nEmpty columns:\n";
      print "--------------\n\n";

      $emptyCount = 0;
      foreach($import->columnNames as $column)
      {
        if (!isset($nonEmptyColumns[$column]))
        {
          print $column .' ';
          $emptyCount++;
        }
      }
      print ($emptyCount) ? '' : "[None]";
    }

    if (count($multiValueColumns))
    {
      print "\n\nMulti-value columns (contain \"|\" character):\n";
      print "-------------------\n\n";

      $displayCount = 1;
      foreach($multiValueColumns as $column => $count)
      {
        print $column .'('. $count .')';
        print ($displayCount < count($multiValueColumns)) ? ', ' : '';
        $displayCount++;
      }
    }

    // Check that parent IDs actually exist if legacy and parent IDs were found
    if (count($legacyIds) && count($parentIds))
    {
      $missingParentIds = array();

      foreach ($parentIds as $parentId => $occurrences)
      {
        // If a legacy ID doesn't exist for a parent ID, then add to missing count
        if (empty($legacyIds[$parentId]))
        {
          $missingParentIds[$parentId] = $occurrences;
        }
      }

      print "\n\nMissing parent IDs:\n";
      print "-------------------\n\n";

      print sprintf("Total unique parent IDs: %d\n\n", count($parentIds));

      print sprintf("Number of unique missing parent IDs: %d\n", count($missingParentIds));

      // Display details about each missing parent and total number of rows affected
      if (count($missingParentIds))
      {
        $orphans = 0;

        foreach ($missingParentIds as $parentId => $numberOfChildren)
        {
          print sprintf("* %d (%d children)\n", $parentId, $numberOfChildren);

          $orphans += $numberOfChildren;
        }

        print sprintf("\nTotal number of rows with missing parents: %d\n", $orphans);
      }
    }

    if ($import->status['numberOfSampleValues'] > 0)
    {
      print "\n\nSample Values:\n";
      print "--------------\n\n";
      foreach($sampleColumnValues as $column => $values)
      {
        print '  '. $column .":";
        if (count($values))
        {
          $shownCount = 0;
          foreach($values as $value)
          {
            print ($shownCount) ? '    ' : ' ';
            print $value ."\n";
            $shownCount++;
          }
        }
        else
        {
          print "    [empty]\n";
        }
      }
    }

    print "\n";
  }
}
