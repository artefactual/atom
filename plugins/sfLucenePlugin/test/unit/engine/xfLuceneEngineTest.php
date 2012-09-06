<?php
/**
 * This file is part of the sfLucene package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require dirname(__FILE__) . '/../../bootstrap/unit.php';
require 'util/xfException.class.php';
require 'util/xfLuceneZendManager.class.php';
require 'engine/xfEngine.interface.php';
require 'engine/xfEngineException.class.php';
require 'engine/xfLuceneEngine.class.php';
require 'engine/xfLuceneHits.class.php';
require 'util/xfLuceneException.class.php';
require 'criteria/xfCriterionTranslator.interface.php';
require 'criteria/xfLuceneCriterionTranslator.class.php';
require 'criteria/xfCriterion.interface.php';
require 'criteria/xfCriterionTerm.class.php';
require 'document/xfDocument.class.php';
require 'document/xfField.class.php';
require 'document/xfFieldValue.class.php';
require 'result/xfDocumentHit.class.php';
require 'result/xfResultException.class.php';
require 'addon/xfLuceneEnhancedFilesystem.class.php';

define('LOCATION', dirname(__file__) . '/../../sandbox/index');

if (is_dir(LOCATION))
{
  // clear the old index first
  foreach (new DirectoryIterator(LOCATION) as $file)
  {
    if ($file->isDot())
    {
      continue;
    }

    unlink($file->getRealpath());
  }
}
else
{
  mkdir(LOCATION, 0777, true);
}

$t = new lime_test(69, new lime_output_color);

$engine = new xfLuceneEngine(LOCATION);

$t->diag('->open(), ->close()');
$engine->open();
$index = $engine->getIndex();
$t->isa_ok($index, 'Zend_Search_Lucene', '->open() opens the index');
$engine->open();
$t->ok($index === $engine->getIndex(), '->open() does not open another index if it is already open');
$engine->close();
try {
  $msg = '->close() closes the index';
  $engine->getIndex();
  $t->fail($msg);
} catch (Exception $e) {
  $t->pass($msg);
}
$engine->open();
$t->isa_ok($engine->getIndex(), 'Zend_Search_Lucene', '->open() can reopen the index');

$t->diag('->getAnalyzer(), ->setAnalyzer()');
$t->isa_ok($engine->getAnalyzer(), 'Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8Num_CaseInsensitive', '->getAnalyzer() is UTF8, number, and case insensitive by default');
$engine->setAnalyzer(new Zend_Search_Lucene_Analysis_Analyzer_Common_Text);
$t->isa_ok($engine->getAnalyzer(), 'Zend_Search_Lucene_Analysis_Analyzer_Common_Text', '->setAnalyzer() can change the analyzer');

$engine->setAnalyzer(new Zend_Search_Lucene_Analysis_Analyzer_Common_Text);
$t->isa_ok($engine->getAnalyzer(), 'Zend_Search_Lucene_Analysis_Analyzer_Common_Text', '->configure() does not change analyzer is no analyzer flag is present');

$t->diag('->enableBatchMode(), ->enableInteractiveMode()');
$engine->enableBatchMode();
$index = $engine->getIndex();
$t->is($index->getMaxBufferedDocs(), 500, '->enableBatchMode() changes max buffered docs');
$t->is($index->getMaxMergeDocs(), PHP_INT_MAX, '->enableBatchMode() changes max merge docs');
$t->is($index->getMergeFactor(), 100, '->enableBatchMode() changes merge factor');
$engine->enableInteractiveMode();
$t->is($index->getMaxBufferedDocs(), 10, '->enableInteractiveMode() changes max buffered docs');
$t->is($index->getMaxMergeDocs(), PHP_INT_MAX, '->enableInteractiveMode() changes max merge docs');
$t->is($index->getMergeFactor(), 10, '->enableInteractiveMode() changes merge factor');

$t->diag('->rewriteDocument()');
$doc = new xfDocument('guid');
$response = $engine->rewriteDocument($doc);
$t->isa_ok($response, 'Zend_Search_Lucene_Document', '->rewriteDocument() creates a Zend_Search_Lucene_Document');
$t->is($response->getFieldValue('__guid'), 'guid', '->rewriteDocument() writes the GUID correctly');
$fields = array(
  xfField::STORED => 'isStored',
  xfField::INDEXED => 'isIndexed',
  xfField::TOKENIZED => 'isTokenized',
  xfField::BINARY => 'isBinary'
);
foreach ($fields as $type => $property)
{
  $name = 'type' . $type;
  $doc->addField(new xfFieldValue(new xfField($name, $type), 'bar'));
  $response = $engine->rewriteDocument($doc)->getField($name);
  $t->ok($response->$property, '->rewriteDocument() can handle "' . $property . '"');
  $others = $fields;
  unset($others[$type]);
  foreach ($others as $notproperty)
  {
    $t->ok(!$response->$notproperty, '->rewriteDocument() does not mark "' . $notproperty . '" with "' . $property . '"');
  }
}
$field = new xfField('foo', xfField::KEYWORD);
$field->setBoost(4);
$doc->addField(new xfFieldValue($field, 'bar', 'ascii'));
$field = $engine->rewriteDocument($doc)->getField('foo');
$t->is($field->name, 'foo', '->rewriteDocument() rewrites the name');
$t->is($field->value, 'bar', '->rewriteDocument() rewrites the value');
$t->is($field->encoding, 'ascii', '->rewriteDocument() rewrites the encoding');
$t->is($field->boost, 4, '->rewriteDocument() rewrites the boost');
$child = new xfDocument('child');
$doc->addChild($child);
$t->is($engine->rewriteDocument($doc)->getField('__sub_documents')->value, serialize(array('child')), '->rewriteDocument() caches child GUID');

$t->diag('->unwriteDocument()');
$doc = new Zend_Search_Lucene_Document;
$doc->addField(Zend_Search_Lucene_Field::Keyword('__guid', 'guid'));
$doc->addField(Zend_Search_Lucene_Field::UnIndexed('__boosts', serialize(array())));
$doc->addField(Zend_Search_Lucene_Field::UnIndexed('__sub_documents', serialize(array())));
$response = $engine->unwriteDocument($doc);
$t->isa_ok($response, 'xfDocument', '->unwriteDocument() returns an xfDocument');
$t->is($response->getGuid(), 'guid', '->unwriteDocument() unwrites the GUID');
$fields = array(
  'isStored' => xfField::STORED,
  'isIndexed' => xfField::INDEXED,
  'isTokenized' => xfField::TOKENIZED,
  'isBinary' => xfField::BINARY
);
foreach ($fields as $property => $type)
{
  $name = 'test';
  $field = new Zend_Search_Lucene_Field($name, 'bar', 'utf8', false, false, false, false);
  $field->$property = true;
  $doc->addField($field);
  $doc->addField(Zend_Search_Lucene_Field::UnIndexed('__boosts', serialize(array('test' => 1))));
  $response = $engine->unwriteDocument($doc)->getField('test')->getField()->getType();
  $t->is($response, $type, '->unwriteDocument() can exclusively handle "' . $property . '"');
}

$t->diag('->add()');
$doc = new xfDocument('guid');
$doc->addField(new xfFieldValue(new xfField('name', xfField::KEYWORD), 'carl'));
$doc->addField(new xfFieldValue(new xfField('age', xfField::STORED), 18));
$engine->add($doc);
$engine->commit();
$t->is($engine->count(), 1, '->add() adds a document');

$parent = new xfDocument('parent');
$child = new xfDocument('child');
$pet = new xfDocument('pet');
$parent->addChild($child);
$child->addChild($pet);
$engine->add($parent);
$engine->commit();
$t->is($engine->count(), 4, '->add() adds a document and every subdocument');

$t->diag('->findGuid()');
$doc = $engine->findGuid('guid');
$t->isa_ok($doc, 'xfDocument', '->findGuid() returns an xfDocument');
$t->is($doc->getGuid(), 'guid', '->findGuid() returns the correct document');

$doc = $engine->findGuid('parent');
$children = $doc->getChildren();
$t->is(count($children), 1, '->findGuid() rebuilds subdocuments correctly');
$t->is($children['child']->getGuid(), 'child', '->findGuid() rebuilds subdocuments in correct order');
$children = $children['child']->getChildren();
$t->is($children['pet']->getGuid(), 'pet', '->findGuid() rebuilds subdocuments recursively');

try {
  $msg = '->findGuid() fails if the GUID does not exist';
  $engine->findGuid('foobar');
  $t->fail($msg);
} catch (Exception $e) {
  $t->pass($msg);
}

$t->diag('->find()');
$c = new xfCriterionTerm('carl');
$hits = $engine->find($c);
$t->isa_ok($hits, 'xfLuceneHits', '->find() returns a xfLuceneHits');
$t->is($hits->count(), 1, '->find() rewrites query into something it can understand');

$t->diag('->optimize()');
try {
  $msg = '->optimize() can execute';
  $engine->optimize();
  $t->pass($msg);
} catch (Exception $e)
{
  $t->fail($msg);
}

$t->diag('->describe()');
$i = $engine->describe();
$t->is($i['Engine'], 'sfLucene 0.5-DEV', '->describe() has the correct engine version');
$t->is($i['Implementation'], 'Zend_Search_Lucene 1.5', '->describe() has the correct Zend_Search_Lucene version');
$t->is($i['Location'], LOCATION, '->describe() has the correct location');
$t->is($i['Total Documents'], 4, '->describe() has the correct total number of documents');
$t->is($i['Total Segments'], 1, '->describe() has the correct total number of segments');
$t->is($i['Total Size'], '0.008 MB', '->describe() has the correct total total size');
$t->is($i['Analyzer'], 'Text', '->describe() has the correct analyzer');
class FooAnalyzer extends Zend_Search_Lucene_Analysis_Analyzer_Common_Text
{
}
$engine->setAnalyzer(new FooAnalyzer);
$i = $engine->describe();
$t->is($i['Analyzer'], 'FooAnalyzer', '->describe() does not crop an external analyzer class name');

$t->diag('->delete()');
$engine->commit();
$engine->delete('guid');
$engine->commit();
$t->is($engine->count(), 3, '->delete() removes a document');

$t->diag('->erase()');
$engine->add(new xfDocument('doc'));
$engine->commit();
$engine->erase();
$t->is($engine->count(), 0,  '->erase() empties the index if it is open');
$t->isa_ok($engine->getIndex(), 'Zend_Search_Lucene_Proxy', '->erase() leaves the index open if it is open');
$engine->add(new xfDocument('doc'));
$engine->close();
$engine->erase();
try {
  $msg = '->erase() leaves the index close if it is closed';
  $engine->getIndex();
  $t->fail($msg);
} catch (Exception $e) {
  $t->pass($msg);
}
$engine->open();
$t->is($engine->count(), 0, '->erase() empties the index if it is closed');

$t->diag('->id()');
$t->isa_ok($engine->id(), 'string', '->id() returns a string');

$t->diag('serialize(), unserialize()');
$engine = unserialize(serialize($engine));
$t->isa_ok($engine->getAnalyzer(), 'FooAnalyzer', 'unserialize() restores the analyzer');
$t->is($engine->getLocation(), LOCATION, 'unserialize() restores the location');
