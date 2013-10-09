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

class sfSkosPlugin
{
  public
    $concepts = array();

  public static function parse($doc, $options = array())
  {
    $terms = array();

    libxml_use_internal_errors(true);

    // Report XML errors
    if (!$doc)
    {
      foreach (libxml_get_errors() as $error)
      {
        //TODO echo errors in template. Use custom validator?
        var_dump($error);
      }
    }

    $skos = new sfSkosPlugin;
    $skos->xpath = new DOMXPath($doc);

    // Create Xpath object, register namespaces
    $skos->xpath->registerNamespace('rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
    $skos->xpath->registerNamespace('skos', 'http://www.w3.org/2004/02/skos/core#');
    $skos->xpath->registerNamespace('dc', 'http://purl.org/dc/elements/1.1/');

    // Set taxonomy
    $skos->taxonomy = QubitTaxonomy::getById(QubitTaxonomy::SUBJECT_ID);
    if (isset($options['taxonomy']))
    {
      $skos->taxonomy = $options['taxonomy'];
    }

    $skos->parent = QubitTerm::getById(QubitTerm::ROOT_ID);
    if (isset($options['parent']))
    {
      $skos->parent = $options['parent'];
    }

    // XPath selector for expanded RDF syntax
    $rdfsel = "rdf:Description[rdf:type[@rdf:resource='http://www.w3.org/2004/02/skos/core#Concept']]";

    // Get all concepts
    $concepts = $skos->xpath->query("skos:Concept | $rdfsel");

    // Create terms from concepts
    foreach ($concepts as $concept)
    {
      if (!($concept instanceof domElement))
      {
        continue;
      }

      $skos->addTerm($concept);
    }

    // Built term associations (including hierarchy)
    foreach ($concepts as $concept)
    {
      if (!($concept instanceof domElement))
      {
        continue;
      }

      // Add parent
      if (0 < $skos->xpath->query('./skos:broader', $concept)->length)
      {
        $skos->setParent($concept);
      }
    }

    foreach ($concepts as $concept)
    {
      if (!($concept instanceof domElement))
      {
        continue;
      }

      // Add children
      if (0 < $skos->xpath->query('./skos:narrower', $concept)->length)
      {
        $skos->setChildren($concept);
      }
    }

    foreach ($concepts as $concept)
    {
      if (!($concept instanceof domElement))
      {
        continue;
      }

      // Add relations
      if (0 < $skos->xpath->query('./skos:related', $concept)->length)
      {
        $skos->addTermRelations($concept);
      }
    }

    return $skos;
  }

  protected function addTerm($concept)
  {
    $term = new QubitTerm;
    $term->taxonomy = $this->taxonomy;

    // Parent to current root (we'll update later)
    $term->parent = $this->parent;

    // Preferred label
    $prefLabels = $this->xpath->query('./skos:prefLabel', $concept);

    foreach ($prefLabels as $prefLabel)
    {
      $value = self::setI18nValue($term, $prefLabel);

      if (isset($value))
      {
        $validName = $value;
      }
    }

    // Don't save a term with no valid name
    if (!isset($validName))
    {
      return;
    }

    // Alternate labels
    foreach ($this->xpath->query('./skos:altLabel', $concept) as $altLabel)
    {
      $otherName = new QubitOtherName;
      $otherName->typeId = QubitTerm::ALTERNATIVE_LABEL_ID;

      $value = self::setI18nValue($otherName, $altLabel);

      if (isset($value))
      {
        $term->otherNames[] = $otherName;
      }

      unset($otherName);
    }

    // URI - save as source note
    $uri = $concept->getAttributeNodeNS('http://www.w3.org/1999/02/22-rdf-syntax-ns#', 'about');
    if ($uri instanceof DOMAttr)
    {
      $note = new QubitNote;
      $note->typeId = QubitTerm::SOURCE_NOTE_ID;
      $note->content = $uri->nodeValue;

      $term->notes[] = $note;

      unset($note);
    }

    // Scope notes
    foreach ($this->xpath->query('./skos:scopeNote', $concept) as $scopeNote)
    {
      $note = new QubitNote;
      $note->typeId = QubitTerm::SCOPE_NOTE_ID;

      $value = self::setI18nValue($note, $scopeNote);

      if (isset($value))
      {
        $term->notes[] = $note;
      }

      unset($note);
    }

    // Map dc.coverage to term.code for place terms
    // Hacky Hackerton was here
    if (QubitTaxonomy::PLACE_ID == $this->taxonomy->getId()) {
      foreach ($this->xpath->query('./dc:coverage', $concept) as $coverage)
      {
          $term->code = $coverage->nodeValue;
      }
    }

    // Save the term
    $term->save();

    // Hash to store concept to term mapping
    $this->terms[$uri->nodeValue] = $term;

    return $this;
  }

  protected function addTermRelations($concept)
  {
    $subjectUri = $concept->getAttributeNodeNS('http://www.w3.org/1999/02/22-rdf-syntax-ns#', 'about');
    if (!($subjectUri instanceof DOMAttr) || !isset($this->terms[$subjectUri->nodeValue]))
    {
      continue;
    }

    foreach ($this->xpath->query('./skos:related', $concept) as $related)
    {
      $objectUri = $related->getAttributeNodeNS('http://www.w3.org/1999/02/22-rdf-syntax-ns#', 'resource');
      if (!($objectUri instanceof DomAttr) || !isset($this->terms[$objectUri->nodeValue]))
      {
        continue;
      }

      // Don't duplicate relationship
      foreach ($this->relations as $r)
      {
        if (
          $r['subject'] == $objectUri->nodeValue && $r['object'] == $subjectUri->nodeValue ||
          $r['subject'] == $subjectUri->nodeValue && $r['object'] == $objectUri->nodeValue)
        {
          continue 2;
        }
      }

      $relation = new QubitRelation;
      $relation->typeId = QubitTerm::TERM_RELATION_ASSOCIATIVE_ID;
      $relation->subject = $this->terms[$subjectUri->nodeValue];
      $relation->object = $this->terms[$objectUri->nodeValue];

      $relation->save();

      $this->relations[] = array('subject' => $subjectUri->nodeValue, 'object' => $objectUri->nodeValue);
    }

    return $this;
  }

  protected function setParent($concept)
  {
    $uri = $concept->getAttributeNodeNS('http://www.w3.org/1999/02/22-rdf-syntax-ns#', 'about');

    if (!isset($this->terms[$uri->nodeValue]))
    {
      return;
    }

    // If term doesn't have default parentId, then assume it's already be set
    if ($this->parent->id != $this->terms[$uri->nodeValue]->parentId)
    {
      return;
    }

    foreach($this->xpath->query('./skos:broader', $concept) as $broader)
    {
      if (!($broader instanceof DOMElement))
      {
        continue;
      }

      $parentUri = $broader->getAttributeNodeNS('http://www.w3.org/1999/02/22-rdf-syntax-ns#', 'resource');

      if (!isset($this->terms[$parentUri->nodeValue]))
      {
        continue;
      }

      if ($parentUri instanceof DOMAttr)
      {
        $this->terms[$uri->nodeValue]->parent = $this->terms[$parentUri->nodeValue];
        $this->terms[$uri->nodeValue]->save();
      }

      return; // Only allowed one parent
    }
  }

  protected function setChildren($concept)
  {
    $uri = $concept->getAttributeNodeNS('http://www.w3.org/1999/02/22-rdf-syntax-ns#', 'about');

    if (!isset($this->terms[$uri->nodeValue]))
    {
      return;
    }

    foreach($this->xpath->query('./skos:narrower', $concept) as $narrower)
    {
      if (!($narrower instanceof DOMElement))
      {
        continue;
      }

      $childUri = $narrower->getAttributeNodeNS('http://www.w3.org/1999/02/22-rdf-syntax-ns#', 'resource');

      if ($childUri instanceof DOMAttr)
      {
        // Skip if child doesn't exists, or it has already been parented
        if (!isset($this->terms[$childUri->nodeValue]) ||
          $this->terms[$uri->nodeValue]->id == $this->terms[$childUri->nodeValue]->parentId)
        {
          continue;
        }

        $this->terms[$childUri->nodeValue]->parent = $this->terms[$uri->nodeValue];
        $this->terms[$childUri->nodeValue]->save();
      }
    }
  }

  protected static function getTermBySourceNote($sourceNote)
  {
    $criteria = new Criteria;
    $criteria->addJoin(QubitTerm::ID, QubitNote::OBJECT_ID);
    $criteria->addJoin(QubitNote::ID, QubitNoteI18n::ID);
    $criteria->add(QubitNoteI18n::CONTENT, $sourceNote);

    return QubitTerm::getOne($criteria);
  }

  protected static function setI18nValue($obj, $domNode)
  {
    if (!($domNode instanceof DOMElement))
    {
      return;
    }

    switch (get_class($obj))
    {
      case 'QubitNote':
        $colName = 'content';
        break;

      default:
        $colName = 'name';
    }

    // Check for xml:lang attribute
    if (null !== $langNode = $domNode->attributes->getNamedItem('lang'))
    {
      $message = $domNode->nodeValue;
      $culture = $langNode->nodeValue;
    }

    else
    {
      $message = $domNode->nodeValue;
    }

    $obj->__set($colName, $message, array('culture' => $culture));

    if (isset($culture) && !isset($term->sourceCulture))
    {
      $obj->sourceCulture = $culture;
    }

    return $obj->__get($colName, array('culture' => $culture));
  }
}
