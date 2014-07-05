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

class ApiTaxonomiesBrowseAction extends QubitApiAction
{
  protected function get($request)
  {
    $taxonomy = QubitTaxonomy::getById($request->id);
    if (null === $taxonomy)
    {
      throw new QubitApi404Exception('Taxonomy not found');
    }

    $terms = array();
    foreach (QubitTaxonomy::getTaxonomyTerms($taxonomy->id) as $term)
    {
      $item = array();

      if (isset($request->culture))
      {
        $name = $term->getName(array('culture' => $request->culture, 'cultureFallback' => true));
      }
      else
      {
        $name = $term->getName(array('cultureFallback' => true));
      }

      $notes = array();
      foreach ($term->getNotesByType($options = array('noteTypeId' => QubitTerm::SCOPE_NOTE_ID)) as $note)
      {
        if (isset($request->culture))
        {
          $notes[] = $note->getContent(array('culture' => $request->culture, 'cultureFallback' => true));
        }
        else
        {
          $notes[] = $note->getContent(array('cultureFallback' => true));
        }
      }

      $this->addItemToArray($item, 'name', $name);
      $this->addItemToArray($item, 'notes', $notes);

      $terms[] = $item;
    }

    return $terms;
  }
}
