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

require_once dirname(__FILE__).'/../../../../test/bootstrap/unit.php';

$t = new lime_test(14, new lime_output_color);

$t->diag('Initializing configuration.');
$configuration = ProjectConfiguration::getApplicationConfiguration('qubit', 'test', true);
sfContext::createInstance($configuration);

// Really small vocabulary in Turtle
$vocabSimple = <<<EOT
@prefix skos: <http://www.w3.org/2004/02/skos/core#> .

<http://example.com/foo>
  a skos:Concept ;
  skos:prefLabel "Foo" .

<http://example.com/bar>
  a skos:Concept ;
  skos:related <http://example.com/foo> ;
  skos:prefLabel "Bar ORIGINAL" ;
  skos:prefLabel "Bar ESPAÑOL"@es .
EOT;

// CSS2 vocabulary in RDF/XML
$vocabCSS2 = <<<EOT
<?xml version="1.0" encoding="utf-8"?>
<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:doc="http://www.w3.org/2000/10/swap/pim/doc#" xmlns:rec="http://www.w3.org/2001/02pd/rec54#" xmlns:contact="http://www.w3.org/2000/10/swap/pim/contact#" xmlns:glos="http://www.w3.org/2003/03/glossary-project/schema#" xmlns:skos="http://www.w3.org/2004/02/skos/core#">
<rdf:Description rdf:about="">
  <dc:rights xmlns:dc="http://purl.org/dc/elements/1.1/" rdf:resource="http://www.w3.org/Consortium/Legal/2002/copyright-documents-20021231" />
</rdf:Description>
  <rdf:Description rdf:about="http://www.w3.org/TR/REC-CSS2">
    <dc:date>1998-05-12</dc:date>
    <dc:title>Glossary of Cascading Style Sheets, level 2 CSS2 Specification</dc:title>
    <doc:version>http://www.w3.org/TR/1998/REC-CSS2-19980512</doc:version>
  </rdf:Description>
  <skos:Concept xmlns="http://www.w3.org/1999/xhtml" rdf:about="http://www.w3.org/2003/03/glossary-project/data/glossaries/CSS2#styleSheet">
    <skos:prefLabel xml:lang="en">style sheet</skos:prefLabel>
    <skos:definition xml:lang="en">A set of statements that specify presentation of a document. Style sheets may have three different origins: author, user, and user agent. The interaction of these sources is described in the section on cascading and inheritance.</skos:definition>
    <rdfs:isDefinedBy rdf:resource="http://www.w3.org/TR/1998/REC-CSS2-19980512"/>
  </skos:Concept>
  <skos:Concept xmlns="http://www.w3.org/1999/xhtml" rdf:about="http://www.w3.org/2003/03/glossary-project/data/glossaries/CSS2#validStyleSheet">
    <skos:prefLabel xml:lang="en">valid style sheet</skos:prefLabel>
    <skos:altLabel xml:lang="ru">Распределение по потокам</skos:altLabel>
    <skos:definition xml:lang="en">The validity of a style sheet depends on the level of CSS used for the style sheet. All valid CSS1 style sheets are valid CSS2 style sheets. However, some changes from CSS1 mean that a few CSS1 style sheets will have slightly different semantics in CSS2. A valid CSS2 style sheet must be written according to the grammar of CSS2. Furthermore, it must contain only at-rules, property names, and property values defined in this specification. An illegal (invalid) at-rule, property name, or property value is one that is not valid.</skos:definition>
    <rdfs:isDefinedBy rdf:resource="http://www.w3.org/TR/1998/REC-CSS2-19980512"/>
  </skos:Concept>
  <skos:Concept xmlns="http://www.w3.org/1999/xhtml" rdf:about="http://www.w3.org/2003/03/glossary-project/data/glossaries/CSS2#sourceDocument">
    <skos:prefLabel xml:lang="en">source document</skos:prefLabel>
    <skos:definition xml:lang="en">The document to which one or more style sheets refer. This is encoded in some language that represents the document as a tree of elements. Each element consists of a name that identifies the type of element, optionally a number of attributes, and a (possibly empty) content.</skos:definition>
    <rdfs:isDefinedBy rdf:resource="http://www.w3.org/TR/1998/REC-CSS2-19980512"/>
  </skos:Concept>
  <skos:Concept xmlns="http://www.w3.org/1999/xhtml" rdf:about="http://www.w3.org/2003/03/glossary-project/data/glossaries/CSS2#documentLanguage">
    <skos:prefLabel xml:lang="en">document language</skos:prefLabel>
    <skos:definition xml:lang="en">The encoding language of the source document (e.g., HTML or an XML application).</skos:definition>
    <rdfs:isDefinedBy rdf:resource="http://www.w3.org/TR/1998/REC-CSS2-19980512"/>
  </skos:Concept>
  <skos:Concept xmlns="http://www.w3.org/1999/xhtml" rdf:about="http://www.w3.org/2003/03/glossary-project/data/glossaries/CSS2#element">
    <skos:prefLabel xml:lang="en">element</skos:prefLabel>
    <skos:definition xml:lang="en">(An SGML term, see [ISO8879].) The primary syntactic constructs of the document language. Most CSS style sheet rules use the names of these elements (such as "P", "TABLE", and "OL" for HTML) to specify rendering information for them.</skos:definition>
    <rdfs:isDefinedBy rdf:resource="http://www.w3.org/TR/1998/REC-CSS2-19980512"/>
  </skos:Concept>
  <skos:Concept xmlns="http://www.w3.org/1999/xhtml" rdf:about="http://www.w3.org/2003/03/glossary-project/data/glossaries/CSS2#replacedElement">
    <skos:prefLabel xml:lang="en">replaced element</skos:prefLabel>
    <skos:definition xml:lang="en">An element for which the CSS formatter knows only the intrinsic dimensions. In HTML, IMG, INPUT, TEXTAREA, SELECT, and OBJECT elements can be examples of replaced elements. For example, the content of the IMG element is often replaced by the image that the "src" attribute designates. CSS does not define how the intrinsic dimensions are found.</skos:definition>
    <rdfs:isDefinedBy rdf:resource="http://www.w3.org/TR/1998/REC-CSS2-19980512"/>
  </skos:Concept>
  <skos:Concept xmlns="http://www.w3.org/1999/xhtml" rdf:about="http://www.w3.org/2003/03/glossary-project/data/glossaries/CSS2#intrinsicDimensions">
    <skos:prefLabel xml:lang="en">intrinsic dimensions</skos:prefLabel>
    <skos:definition xml:lang="en">The width and height as defined by the element itself, not imposed by the surroundings. In CSS2 it is assumed that all replaced elements -- and only replaced elements -- come with intrinsic dimensions.</skos:definition>
    <rdfs:isDefinedBy rdf:resource="http://www.w3.org/TR/1998/REC-CSS2-19980512"/>
  </skos:Concept>
  <skos:Concept xmlns="http://www.w3.org/1999/xhtml" rdf:about="http://www.w3.org/2003/03/glossary-project/data/glossaries/CSS2#attribute">
    <skos:prefLabel xml:lang="en">attribute</skos:prefLabel>
    <skos:definition xml:lang="en">A value associated with an element, consisting of a name, and an associated (textual) value.</skos:definition>
    <rdfs:isDefinedBy rdf:resource="http://www.w3.org/TR/1998/REC-CSS2-19980512"/>
  </skos:Concept>
  <skos:Concept xmlns="http://www.w3.org/1999/xhtml" rdf:about="http://www.w3.org/2003/03/glossary-project/data/glossaries/CSS2#content">
    <skos:prefLabel xml:lang="en">content</skos:prefLabel>
    <skos:definition xml:lang="en">The content associated with an element in the source document; not all elements have content in which case they are called empty. The content of an element may include text, and it may include a number of sub-elements, in which case the element is called the parent of those sub-elements.</skos:definition>
    <rdfs:isDefinedBy rdf:resource="http://www.w3.org/TR/1998/REC-CSS2-19980512"/>
  </skos:Concept>
  <skos:Concept xmlns="http://www.w3.org/1999/xhtml" rdf:about="http://www.w3.org/2003/03/glossary-project/data/glossaries/CSS2#renderedContent">
    <skos:prefLabel xml:lang="en">rendered content</skos:prefLabel>
    <skos:definition xml:lang="en">The content of an element after the rendering that applies to it according to the relevant style sheets has been applied. The rendered content of a replaced element comes from outside the source document. Rendered content may also be alternate text for an element (e.g., the value of the HTML "alt" attribute), and may include items inserted implicitly or explicitly by the style sheet, such as bullets, numbering, etc.</skos:definition>
    <rdfs:isDefinedBy rdf:resource="http://www.w3.org/TR/1998/REC-CSS2-19980512"/>
  </skos:Concept>
  <skos:Concept xmlns="http://www.w3.org/1999/xhtml" rdf:about="http://www.w3.org/2003/03/glossary-project/data/glossaries/CSS2#documentTree">
    <skos:prefLabel xml:lang="en">document tree</skos:prefLabel>
    <skos:definition xml:lang="en">The tree of elements encoded in the source document. Each element in this tree has exactly one parent, with the exception of the root element, which has none.</skos:definition>
    <rdfs:isDefinedBy rdf:resource="http://www.w3.org/TR/1998/REC-CSS2-19980512"/>
  </skos:Concept>
  <skos:Concept xmlns="http://www.w3.org/1999/xhtml" rdf:about="http://www.w3.org/2003/03/glossary-project/data/glossaries/CSS2#child">
    <skos:prefLabel xml:lang="en">child</skos:prefLabel>
    <skos:definition xml:lang="en">An element A is called the child of element B if an only if B is the parent of A.</skos:definition>
    <rdfs:isDefinedBy rdf:resource="http://www.w3.org/TR/1998/REC-CSS2-19980512"/>
  </skos:Concept>
  <skos:Concept xmlns="http://www.w3.org/1999/xhtml" rdf:about="http://www.w3.org/2003/03/glossary-project/data/glossaries/CSS2#descendant">
    <skos:prefLabel xml:lang="en">descendant</skos:prefLabel>
    <skos:definition xml:lang="en">An element A is called a descendant of an element B, if either (1) A is a child of B, or (2) A is the child of some element C that is a descendant of B.</skos:definition>
    <rdfs:isDefinedBy rdf:resource="http://www.w3.org/TR/1998/REC-CSS2-19980512"/>
  </skos:Concept>
  <skos:Concept xmlns="http://www.w3.org/1999/xhtml" rdf:about="http://www.w3.org/2003/03/glossary-project/data/glossaries/CSS2#ancestor">
    <skos:prefLabel xml:lang="en">ancestor</skos:prefLabel>
    <skos:definition xml:lang="en">An element A is called an ancestor of an element B, if and only if B is a descendant of A.</skos:definition>
    <rdfs:isDefinedBy rdf:resource="http://www.w3.org/TR/1998/REC-CSS2-19980512"/>
  </skos:Concept>
  <skos:Concept xmlns="http://www.w3.org/1999/xhtml" rdf:about="http://www.w3.org/2003/03/glossary-project/data/glossaries/CSS2#sibling">
    <skos:prefLabel xml:lang="en">sibling</skos:prefLabel>
    <skos:definition xml:lang="en">An element A is called a sibling of an element B, if and only if B and A share the same parent element. Element A is a preceding sibling if it comes before B in the document tree. Element B is a following sibling if it comes after B in the document tree.</skos:definition>
    <rdfs:isDefinedBy rdf:resource="http://www.w3.org/TR/1998/REC-CSS2-19980512"/>
  </skos:Concept>
  <skos:Concept xmlns="http://www.w3.org/1999/xhtml" rdf:about="http://www.w3.org/2003/03/glossary-project/data/glossaries/CSS2#precedingElement">
    <skos:prefLabel xml:lang="en">preceding element</skos:prefLabel>
    <skos:definition xml:lang="en">An element A is called a preceding element of an element B, if and only if (1) A is an ancestor of B or (2) A is a preceding sibling of B.</skos:definition>
    <rdfs:isDefinedBy rdf:resource="http://www.w3.org/TR/1998/REC-CSS2-19980512"/>
  </skos:Concept>
  <skos:Concept xmlns="http://www.w3.org/1999/xhtml" rdf:about="http://www.w3.org/2003/03/glossary-project/data/glossaries/CSS2#followingElement">
    <skos:prefLabel xml:lang="en">following element</skos:prefLabel>
    <skos:definition xml:lang="en">An element A is called a following element of an element B, if and only if B is a preceding element of A.</skos:definition>
    <rdfs:isDefinedBy rdf:resource="http://www.w3.org/TR/1998/REC-CSS2-19980512"/>
  </skos:Concept>
  <skos:Concept xmlns="http://www.w3.org/1999/xhtml" rdf:about="http://www.w3.org/2003/03/glossary-project/data/glossaries/CSS2#author">
    <skos:prefLabel xml:lang="en">author</skos:prefLabel>
    <skos:definition xml:lang="en">An author is a person who writes documents and associated style sheets. An authoring tool generates documents and associated style sheets.</skos:definition>
    <rdfs:isDefinedBy rdf:resource="http://www.w3.org/TR/1998/REC-CSS2-19980512"/>
  </skos:Concept>
  <skos:Concept xmlns="http://www.w3.org/1999/xhtml" rdf:about="http://www.w3.org/2003/03/glossary-project/data/glossaries/CSS2#user">
    <skos:prefLabel xml:lang="en">user</skos:prefLabel>
    <skos:definition xml:lang="en">A user is a person who interacts with a user agent to view, hear, or otherwise use a document and its associated style sheet. The user may provide a personal style sheet that encodes personal preferences.A user agent is any program that interprets a document written in the document language and applies associated style sheets according to the terms of this specification. A user agent may display a document, read it aloud, cause it to be printed, convert it to another format, etc.</skos:definition>
    <rdfs:isDefinedBy rdf:resource="http://www.w3.org/TR/1998/REC-CSS2-19980512"/>
  </skos:Concept>
  <skos:Concept xmlns="http://www.w3.org/1999/xhtml" rdf:about="http://www.w3.org/2003/03/glossary-project/data/glossaries/CSS2#userAgentUA">
    <skos:prefLabel xml:lang="en">user agent (UA)</skos:prefLabel>
    <skos:definition xml:lang="en">A user agent is any program that interprets a document written in the document language and applies associated style sheets according to the terms of this specification. A user agent may display a document, read it aloud, cause it to be printed, convert it to another format, etc.</skos:definition>
    <rdfs:isDefinedBy rdf:resource="http://www.w3.org/TR/1998/REC-CSS2-19980512"/>
  </skos:Concept>
</rdf:RDF>
EOT;

function toDataScheme($string)
{
  return 'data://text/plain;base64,'.base64_encode(trim($string));
}

function withTransaction($callback)
{
  try
  {
    $conn = Propel::getConnection();
    $conn->beginTransaction();

    return call_user_func($callback, $conn);
  }
  finally
  {
    $conn->rollBack();
  }
}

withTransaction(function($conn) use ($t, $vocabCSS2)
{
  // Make sure that Russian is not defined as a supported language
  $criteria = new Criteria;
  $criteria->add(QubitSetting::NAME, 'ru');
  $criteria->add(QubitSetting::SCOPE, 'i18n_languages');
  if (null !== $term = QubitTerm::getOne($criteria))
  {
    $term->delete();
  }

  $term = new QubitTerm;
  $term->parentId = QubitTerm::ROOT_ID;
  $term->taxonomyId = QubitTaxonomy::SUBJECT_ID;
  $term->save();
  $termId = $term->id;

  $importer = new sfSkosPlugin(QubitTaxonomy::SUBJECT_ID, array('parentId' => $termId));
  $importer->load(toDataScheme($vocabCSS2));
  $importer->importGraph();

  QubitTerm::clearCache();
  $term = QubitTerm::getById($termId);

  $t->is(
    floor(($term->rgt - $term->lft) / 2),
    count($importer->getGraph()->allOfType('skos:Concept')),
    'Graph concept count and database descendants count using lft/rgt match');

  $t->is(
    count($term->getDescendants()),
    count($importer->getGraph()->allOfType('skos:Concept')),
    'Graph concept count and database descendants match');

  $criteria = new Criteria;
  $criteria->add(QubitTerm::TAXONOMY_ID, QubitTaxonomy::SUBJECT_ID);
  $criteria->addJoin(QubitTerm::ID, QubitTermI18n::ID);
  $criteria->add(QubitTermI18n::NAME, 'user agent (UA)');
  $term = QubitTerm::getOne($criteria);

  $t->is(get_class($term), 'QubitTerm', 'skos:Concept is created');
  $t->is($term->getName(array('culture' => 'en')), 'user agent (UA)', 'skos:Concept\'s prefLabel matches the term name');

  $t->is($importer->hasErrors(), true, 'sfSkosPlugin has errors');
  $t->is(count($importer->getErrors()), 1, 'sfSkosPlugin has *one* error');
  $errors = $importer->getErrors();
  $t->is($errors[0], 'The following languages are used in the dataset imported but not supported by AtoM: ru', 'There is an error about Russian being not defined in AtoM');
});


withTransaction(function($conn) use ($t, $vocabSimple)
{
  // Count existing subjects that are children of the root term
  $criteria = new Criteria;
  $criteria->add(QubitTerm::TAXONOMY_ID, QubitTaxonomy::SUBJECT_ID);
  $criteria->add(QubitTerm::PARENT_ID, QubitTerm::ROOT_ID);
  $termCount1 = count(QubitTerm::get($criteria));

  // Import graph
  $importer = new sfSkosPlugin(QubitTaxonomy::SUBJECT_ID);
  $importer->load(toDataScheme($vocabSimple));
  $importer->importGraph();

  $graph = $importer->getGraph();
  $conceptCount = count($graph->allOfType('skos:Concept'));
  $t->is($conceptCount, 2, '$vocabSimple has two concepts');

  // Test that there are two extra subjects after importing the new dataset
  $terms = QubitTerm::get($criteria);
  $termCount2 = count($terms);
  $t->is($termCount1 + $conceptCount, $termCount2, 'Subject taxonomy contains the new concepts in the dataset');

  $match = null;
  foreach ($terms as $item)
  {
    if ($item->getName(array('culture' => 'es')) == 'Bar ESPAÑOL')
    {
      $match = $item;
      break;
    }
  }
  $t->is(get_class($match), 'QubitTerm', 'Translations are properly imported too');
});


withTransaction(function($conn) use ($t, $vocabSimple)
{
  // Create subject parent term
  $parent = new QubitTerm;
  $parent->parentId = QubitTerm::ROOT_ID;
  $parent->taxonomyId = QubitTaxonomy::SUBJECT_ID;
  $parent->sourceCulture = 'eu'; // Basque!
  $parent->setName('proba', array('culture' => 'eu'));
  $parent->save();

  // Import graph
  $importer = new sfSkosPlugin(QubitTaxonomy::SUBJECT_ID, array('parentId' => $parent->id));
  $importer->load(toDataScheme($vocabSimple));
  $importer->importGraph();

  // Populate parent term again
  QubitTerm::clearCache();
  $parent = QubitTerm::getById($parent->id);

  // Test hierarchy
  $t->is(count($parent->getDescendants()), count($importer->getGraph()->allOfType('skos:Concept')), 'Term container reflects new hierarchy');

  // Test search
  $search = QubitSearch::getInstance();
  $search->flushBatch();

  foreach ($parent->getDescendants() as $key => $item)
  {
    try
    {
      $search->index->getType('QubitTerm')->getDocument($item->id);
      $t->pass("Term ${key} is indexed");
    }
    catch (Elastica\Exception\NotFoundException $e) 
    {
      $t->fail("Term ${key} was not indexed");
    }
  }

  $doc = $search->index->getType('QubitTerm')->getDocument($parent->id)->getData();
  $t->is($doc['numberOfDescendants'], count($importer->getGraph()->allOfType('skos:Concept')), 'Parent term ES document :numberOfDescendants: field is up to date');

});
