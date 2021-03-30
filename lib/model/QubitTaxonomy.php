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

class QubitTaxonomy extends BaseTaxonomy
{
    public const ROOT_ID = 30;
    public const DESCRIPTION_DETAIL_LEVEL_ID = 31;
    public const ACTOR_ENTITY_TYPE_ID = 32;
    public const DESCRIPTION_STATUS_ID = 33;
    public const LEVEL_OF_DESCRIPTION_ID = 34;
    public const SUBJECT_ID = 35;
    public const ACTOR_NAME_TYPE_ID = 36;
    public const NOTE_TYPE_ID = 37;
    public const REPOSITORY_TYPE_ID = 38;
    public const EVENT_TYPE_ID = 40;
    public const QUBIT_SETTING_LABEL_ID = 41;
    public const PLACE_ID = 42;
    public const FUNCTION_ID = 43;
    public const HISTORICAL_EVENT_ID = 44;
    public const COLLECTION_TYPE_ID = 45;
    public const MEDIA_TYPE_ID = 46;
    public const DIGITAL_OBJECT_USAGE_ID = 47;
    public const PHYSICAL_OBJECT_TYPE_ID = 48;
    public const RELATION_TYPE_ID = 49;
    public const MATERIAL_TYPE_ID = 50;
    // Rules for Archival Description (RAD) taxonomies
    public const RAD_NOTE_ID = 51;
    public const RAD_TITLE_NOTE_ID = 52;
    public const MODS_RESOURCE_TYPE_ID = 53;
    public const DC_TYPE_ID = 54;
    public const ACTOR_RELATION_TYPE_ID = 55;
    public const RELATION_NOTE_TYPE_ID = 56;
    public const TERM_RELATION_TYPE_ID = 57;
    public const STATUS_TYPE_ID = 59;
    public const PUBLICATION_STATUS_ID = 60;
    public const ISDF_RELATION_TYPE_ID = 61;
    // Accession taxonomies
    public const ACCESSION_RESOURCE_TYPE_ID = 62;
    public const ACCESSION_ACQUISITION_TYPE_ID = 63;
    public const ACCESSION_PROCESSING_PRIORITY_ID = 64;
    public const ACCESSION_PROCESSING_STATUS_ID = 65;
    public const DEACCESSION_SCOPE_ID = 66;
    // Right taxonomies
    public const RIGHT_ACT_ID = 67;
    public const RIGHT_BASIS_ID = 68;
    public const COPYRIGHT_STATUS_ID = 69;
    // Metadata templates
    public const INFORMATION_OBJECT_TEMPLATE_ID = 70;
    public const AIP_TYPE_ID = 71;
    public const THEMATIC_AREA_ID = 72;
    public const GEOGRAPHIC_SUBREGION_ID = 73;
    // DACS notes
    public const DACS_NOTE_ID = 74;
    // PREMIS Rights Statues
    public const RIGHTS_STATUTES_ID = 75;
    // Genre taxonomy
    public const GENRE_ID = 78;
    public const JOB_STATUS_ID = 79;
    public const ACTOR_OCCUPATION_ID = 80;
    public const USER_ACTION_ID = 81;
    public const ACCESSION_ALTERNATIVE_IDENTIFIER_TYPE_ID = 82;
    public const ACCESSION_EVENT_TYPE_ID = 83;

    public $disableNestedSetUpdating = false;

    public static $lockedTaxonomies = [
        self::QUBIT_SETTING_LABEL_ID,
        self::COLLECTION_TYPE_ID,
        self::DIGITAL_OBJECT_USAGE_ID,
        self::MEDIA_TYPE_ID,
        self::RELATION_TYPE_ID,
        self::RELATION_NOTE_TYPE_ID,
        self::TERM_RELATION_TYPE_ID,
        self::ROOT_ID,
        self::STATUS_TYPE_ID,
        self::PUBLICATION_STATUS_ID,
        self::ACTOR_NAME_TYPE_ID,
        self::INFORMATION_OBJECT_TEMPLATE_ID,
        self::JOB_STATUS_ID,
    ];

    public function __construct($id = null)
    {
        parent::__construct();

        if (!empty($id)) {
            $this->id = $id;
        }
    }

    public function __toString()
    {
        if (!$this->getName()) {
            return (string) $this->getName(['sourceCulture' => true]);
        }

        return (string) $this->getName();
    }

    public static function getRoot()
    {
        return parent::getById(self::ROOT_ID);
    }

    public static function addEditableTaxonomyCriteria($criteria)
    {
        $criteria->add(QubitTaxonomy::ID, self::$lockedTaxonomies, Criteria::NOT_IN);

        return $criteria;
    }

    public static function getEditableTaxonomies()
    {
        $criteria = new Criteria();
        $criteria = self::addEditableTaxonomyCriteria($criteria);

        // Add criteria to sort by name with culture fallback
        $criteria->addAscendingOrderByColumn('name');
        $options = ['returnClass' => 'QubitTaxonomy'];
        $criteria = QubitCultureFallback::addFallbackCriteria($criteria, 'QubitTaxonomy', $options);

        return QubitTaxonomy::get($criteria);
    }

    public static function getTaxonomyTerms($taxonomyId, $options = [])
    {
        $criteria = new Criteria();
        $criteria->add(QubitTerm::TAXONOMY_ID, $taxonomyId);

        // Only include top-level terms if option is set
        if (isset($options['level']) && 'top' == $options['level']) {
            $criteria->add(QubitTerm::PARENT_ID, QubitTerm::ROOT_ID);
        }

        // Sort alphabetically
        $criteria->addAscendingOrderByColumn('name');

        // Do source culture fallback
        $criteria = QubitCultureFallback::addFallbackCriteria($criteria, 'QubitTerm');

        return QubitTerm::get($criteria);
    }

    /**
     * Get an associative array of terms.
     *
     * @param null|mixed $connection
     */
    public function getTermsAsArray($connection = null)
    {
        if (empty($this->id)) {
            throw new sfException('Invalid taxonomy id');
        }

        if (!isset($connection)) {
            $connection = Propel::getConnection();
        }

        $sql = 'SELECT
                term.id AS `id`,
                term.taxonomy_id AS `taxonomy_id`,
                term.code AS `code`,
                term.parent_id AS `parent_id`,
                term.lft AS `lft`,
                term.rgt AS `rgt`,
                term.source_culture AS `source_culture`,
                term_i18n.name AS `name`,
                term_i18n.culture as `culture`
            FROM term INNER JOIN term_i18n ON term.id = term_i18n.id
            WHERE term.taxonomy_id = ?
            ORDER BY term_i18n.culture ASC, term_i18n.name ASC;';

        $statement = $connection->prepare($sql);
        $statement->execute([$this->id]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTermNameToIdLookupTable($connection = null)
    {
        $terms = $this->getTermsAsArray($connection);

        if (!is_array($terms) || 0 == count($terms)) {
            return;
        }

        foreach ($terms as $term) {
            // Trim and lowercase values for lookup
            $term = array_map(function ($str) {
                return trim(strtolower($str));
            }, $term);

            $idLookupTable[$term['culture']][$term['name']] = $term['id'];
        }

        return $idLookupTable;
    }

    protected function insert($connection = null)
    {
        if (!isset($this->slug)) {
            $this->slug = QubitSlug::slugify($this->__get('name', ['sourceCulture' => true]));
        }

        return parent::insert($connection);
    }
}
