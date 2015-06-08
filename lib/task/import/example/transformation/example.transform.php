<?php

/*
 * Run from base directory of Qubit using:
 *
 *   ./symfony csv:custom-import \
 *     --import-definition=/path/to/transformation/example.transform.php \
 *     --output-file=/tmp/transformed.csv \
 *     /path/to/example.csv
 *
 */

include(dirname(__FILE__) .'/lib/QubitCsvTransform.class.php');
include(dirname(__FILE__) .'/lib/QubitCsvTransformFactory.class.php');

$addColumns = array(
  'parentId',
  'generalNote'
);

$renameColumns = array(
  'ID' => 'legacyId',
  'TITLE' => 'title',
  'LEVEL' => 'levelOfDescription'
);

$transformLogic = function(&$self)
{
  $self->amalgamateColumns(array(
    'NOTES',
    'More:' => 'MORE NOTES'
  ), 'generalNote');
};

$parentKeyLogic = function(&$self) {
  // if row is a parent, return its key... otherwise return false

  // if row doesn't have a parent in our example, then it itself is a parent
  if (
    !trim($self->columnValue('PARENT'))
  ) {
    return $self->columnValue('title');
  } else {
    return false;
  } 
};

$rowParentKeyLookupLogic = function(&$self) {
  // if row has a parent, figure out the key for it and return it... otherwise
  // return false
  $parentKey = trim($self->columnValue('PARENT'));
  if ($parentKey)
  {
    return $parentKey;
  }
};

$setup = new QubitCsvTransformFactory(array(
  'cliOptions'              => $options,
  'machineName'             => 'accessions',
  'addColumns'              => $addColumns,
  'renameColumns'           => $renameColumns,
  'transformLogic'          => $transformLogic,
  'parentKeyLogic'          => $parentKeyLogic,
  'rowParentKeyLookupLogic' => $rowParentKeyLookupLogic
));

return $setup->make();
