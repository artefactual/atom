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

class QubitValidatorAccessionIdentifier extends sfValidatorBase
{
    public static function identifierCanBeUsed($identifier, $byResource = null)
    {
        $criteria = new Criteria();
        $criteria->add(QubitAccession::IDENTIFIER, $identifier);

        // If accession isn't new, make sure no accession other than it is using proposed identifier
        if (null !== $byResource && isset($byResource->id)) {
            $criteria->add(QubitAccession::ID, $byResource->id, Criteria::NOT_EQUAL);
        }

        return 0 == count(QubitAccession::get($criteria));
    }

    protected function configure($options = [], $messages = [])
    {
        parent::configure($options, $messages);

        $this->addRequiredOption('resource');
    }

    protected function doClean($value)
    {
        // Before allowing use of proposed identifier, make sure it's available for use
        if (self::identifierCanBeUsed($value, $this->getOption('resource'))) {
            return $value;
        }

        throw new sfValidatorError($this, sfContext::getInstance()->i18n->__('This identifier is already in use.'), ['value' => $value]);
    }
}
