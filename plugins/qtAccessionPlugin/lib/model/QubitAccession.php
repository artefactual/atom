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

class QubitAccession extends BaseAccession
{
    // Flag for updating search index on save
    public $indexOnSave = true;

    public function __toString()
    {
        return (string) $this->identifier;
    }

    public function save($connection = null)
    {
        parent::save($connection);

        // Save updated related events (update search index after updating all
        // related objects that are included in the index document)
        foreach ($this->eventsRelatedByobjectId as $item) {
            $item->indexOnSave = false;

            // TODO Needed if $this is new, should be transparent
            $item->object = $this;
            $item->save($connection);
        }

        if ($indexOnSave) {
            QubitSearch::getInstance()->update($this);
        }

        return $this;
    }

    public function delete($connection = null)
    {
        QubitSearch::getInstance()->delete($this);

        return parent::delete($connection);
    }

    public function isAccrual()
    {
        if (!isset($this->id)) {
            return false;
        }

        $criteria = new Criteria();
        $criteria->add(QubitRelation::TYPE_ID, QubitTerm::ACCRUAL_ID);
        $criteria->add(QubitRelation::SUBJECT_ID, $this->id);

        return 0 < count(QubitRelation::get($criteria));
    }

    public static function maskEnabled()
    {
        $setting = QubitSetting::getByName('accession_mask_enabled');

        return null === $setting || boolval($setting->getValue(['sourceCulture' => true]));
    }

    public static function nextAccessionNumber()
    {
        $setting = QubitSetting::getByName('accession_counter');

        return $setting->getValue(['sourceCulture' => true]) + 1;
    }

    public static function incrementAccessionCounter()
    {
        $setting = QubitSetting::getByName('accession_counter');
        $value = $setting->getValue(['sourceCulture' => true]) + 1;
        $setting->setValue($value, ['sourceCulture' => true]);
        $setting->save();
    }

    public static function nextAvailableIdentifier()
    {
        $con = Propel::getConnection();

        try {
            $con->beginTransaction();

            // Determine what should be the next identifier
            $identifier = Qubit::generateIdentifierFromCounterAndMask(self::nextAccessionNumber(), sfConfig::get('app_accession_mask'));

            // If this identifier has already been used, increment counter and try again
            if (!QubitValidatorAccessionIdentifier::identifierCanBeUsed($identifier)) {
                self::incrementAccessionCounter();
                $identifier = self::nextAvailableIdentifier();
            }

            $con->commit();

            return $identifier;
        } catch (PropelException $e) {
            $con->rollback();

            throw $e;
        }
    }

    /**
     * Get related actors.
     *
     * @param mixed $options
     */
    public function getActors($options = [])
    {
        $criteria = new Criteria();
        $criteria->addJoin(QubitActor::ID, QubitEvent::ACTOR_ID);
        $criteria->add(QubitEvent::OBJECT_ID, $this->id);

        if (isset($options['eventTypeId'])) {
            $criteria->add(QubitEvent::TYPE_ID, $options['eventTypeId']);
        }

        if (isset($options['cultureFallback']) && true === $options['cultureFallback']) {
            $criteria->addAscendingOrderByColumn('authorized_form_of_name');
            $criteria = QubitCultureFallback::addFallbackCriteria($criteria, 'QubitActor', $options);
        }

        return QubitActor::get($criteria);
    }

    /**
     * Get creators.
     *
     * @param mixed $options
     */
    public function getCreators($options = [])
    {
        return $this->getActors($options = ['eventTypeId' => QubitTerm::CREATION_ID]);
    }

    /**
     * Related events which have a date.
     */
    public function getDates(array $options = [])
    {
        $criteria = new Criteria();
        $criteria->add(QubitEvent::OBJECT_ID, $this->id);

        $criteria->addMultipleJoin(
            [
                [QubitEvent::ID, QubitEventI18n::ID],
                [QubitEvent::SOURCE_CULTURE, QubitEventI18n::CULTURE],
            ],
            Criteria::LEFT_JOIN
        );

        $criteria->add($criteria->getNewCriterion(QubitEvent::END_DATE, null, Criteria::ISNOTNULL)
            ->addOr($criteria->getNewCriterion(QubitEvent::START_DATE, null, Criteria::ISNOTNULL))
            ->addOr($criteria->getNewCriterion(QubitEventI18n::DATE, null, Criteria::ISNOTNULL)));

        if (isset($options['type_id'])) {
            $criteria->add(QubitEvent::TYPE_ID, $options['type_id']);
        }

        $criteria->addDescendingOrderByColumn(QubitEvent::START_DATE);

        return QubitEvent::get($criteria);
    }

    /**
     * Get alternative identifiers.
     */
    public function getAlternativeIdentifiers()
    {
        $otherNames = [];

        $criteria = new Criteria();
        $criteria->add(QubitOtherName::OBJECT_ID, $this->id);
        $criteria->addJoin(QubitOtherName::TYPE_ID, QubitTerm::ID);
        $criteria->add(QubitTerm::TAXONOMY_ID, QubitTaxonomy::ACCESSION_ALTERNATIVE_IDENTIFIER_TYPE_ID);

        foreach (QubitOtherName::get($criteria) as $otherName) {
            $otherNames[] = $otherName;
        }

        return $otherNames;
    }

    protected function insert($connection = null)
    {
        // If identifier has been specified and the mask is enabled, increment the counter
        if (!empty($this->identifier) && self::maskEnabled()) {
            $con = Propel::getConnection();

            try {
                $con->beginTransaction();
                self::incrementAccessionCounter();
                $con->commit();
            } catch (PropelException $e) {
                $con->rollback();

                throw $e;
            }
        }

        if (!isset($this->slug)) {
            $this->slug = QubitSlug::slugify($this->__get('identifier', ['sourceCulture' => true]));
        }

        parent::insert($connection);
    }
}
