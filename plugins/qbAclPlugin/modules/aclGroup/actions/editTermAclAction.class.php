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

class AclGroupEditTermAclAction extends AclGroupEditDefaultAclAction
{
    /**
     * Define form field names.
     *
     * @var string
     */
    public static $NAMES = [
        'taxonomy',
    ];

    public function execute($request)
    {
        parent::execute($request);

        $this->permissions = [];
        if (null != $this->resource->id) {
            // Get term permissions for this group
            $criteria = new Criteria();
            $criteria->addJoin(QubitAclPermission::OBJECT_ID, QubitObject::ID, Criteria::LEFT_JOIN);
            $criteria->add(QubitAclPermission::GROUP_ID, $this->resource->id);
            $c1 = $criteria->getNewCriterion(QubitAclPermission::OBJECT_ID, null, Criteria::ISNULL);
            $c2 = $criteria->getNewCriterion(QubitObject::CLASS_NAME, 'QubitTerm');
            $c1->addOr($c2);
            $criteria->add($c1);

            $criteria->addAscendingOrderByColumn(QubitAclPermission::CONSTANTS);
            $criteria->addAscendingOrderByColumn(QubitAclPermission::OBJECT_ID);

            if (0 < count($permissions = QubitAclPermission::get($criteria))) {
                $this->permissions = $permissions;
            }
        }

        // List of actions without create or translate
        $this->basicActions = QubitAcl::$ACTIONS;
        unset($this->basicActions['read'], $this->basicActions['translate']);

        if ($request->isMethod('post')) {
            $this->form->bind($request->getPostParameters());

            if ($this->form->isValid()) {
                $this->processForm();
                $this->redirect([$this->resource, 'module' => 'aclGroup', 'action' => 'indexTermAcl']);
            }
        }
    }

    protected function addField($name)
    {
        switch ($name) {
            case 'taxonomy':
                $choices = [];
                $choices[null] = null;

                foreach (QubitTaxonomy::getEditableTaxonomies() as $taxonomy) {
                    $choices[$this->context->routing->generate(null, [$taxonomy, 'module' => 'taxonomy'])] = $taxonomy;
                }

                $this->form->setDefault($name, null);
                $this->form->setValidator($name, new sfValidatorString());
                $this->form->setWidget($name, new sfWidgetFormSelect(['choices' => $choices]));

                break;
        }
    }
}
