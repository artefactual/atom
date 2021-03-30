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

class InformationObjectCalculateDatesLinkComponent extends sfComponent
{
    public function execute($request)
    {
        $i18n = $this->context->i18n;

        // Determine when, or if, the date calculation job was last run
        $criteria = new Criteria();
        $criteria->add(QubitJob::NAME, 'arCalculateDescendantDatesJob');
        $criteria->add(QubitJob::OBJECT_ID, $this->resource->id);
        $criteria->addDescendingOrderByColumn(QubitJob::ID);

        $lastJobRan = QubitJob::getOne($criteria);

        if (null === $lastJobRan) {
            $this->lastRun = $i18n->__('Never');
        } elseif (QubitTerm::JOB_STATUS_IN_PROGRESS_ID == $lastJobRan->statusId) {
            $this->lastRun = $i18n->__('In progress');
        } else {
            $this->lastRun = $lastJobRan->completedAt;
        }
    }
}
