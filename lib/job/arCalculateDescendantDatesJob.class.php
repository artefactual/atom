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

/**
 * A job to calculate, for a given information object and event type, the
 * earliest start date and latest end date.
 */
class arCalculateDescendantDatesJob extends arBaseJob
{
    /**
     * @see arBaseJob::$requiredParameters
     */
    protected $extraRequiredParameters = ['objectId'];

    public function runJob($parameters)
    {
        if (empty($parameters['eventId']) && empty($parameters['eventTypeId'])) {
            throw new sfException('Either the eventId or eventTypeId parameter must be specified.');
        }

        // Output job description (whether an existing event will be modified or a new event, of a given type, created)
        $eventTargetDescription = (empty($parameters['eventId'])) ? 'event type' : 'event ID';
        $eventTargetId = (empty($parameters['eventId'])) ? $parameters['eventTypeId'] : $parameters['eventId'];
        $this->info($this->i18n->__(
            'Calculating dates for information object (ID: %1, %2: %3)',
            ['%1' => $parameters['objectId'], '%2' => $eventTargetDescription, '%3' => $eventTargetId]
        ));

        // Load information object
        $io = QubitInformationObject::getById($parameters['objectId']);

        // Create or load target event
        if (empty($parameters['eventId'])) {
            $event = new QubitEvent();
            $event->objectId = $parameters['objectId'];
            $event->typeId = $parameters['eventTypeId'];
        } else {
            $event = QubitEvent::getById($parameters['eventId']);
        }

        // Describe original dates if replacing date data in an existing event
        if (!empty($parameters['eventId'])) {
            // Describe original dates
            $this->info($this->i18n->__(
                'Original start date of event is %1 and end date is %2.',
                ['%1' => $this->describeDate($event->startDate), '%2' => $this->describeDate($event->endDate)]
            ));
        }

        // Determine earliest start date and lastest end date of descendent events
        // sharing type with provided event
        //
        // Note: if NULL is present as a start or end date then it'll be considered
        //       the minimum or maximum respectively (for open-ended date ranges)
        $sql = 'SELECT
            COUNT(*) AS found,
            IF (MAX(e.start_date IS NULL), NULL, MIN(e.start_date)) AS min,
            IF (MAX(e.end_date IS NULL), NULL, MAX(e.end_date)) as max
            FROM
                information_object i
                INNER JOIN event e ON i.id=e.object_id
            WHERE
                i.lft > :lft
                AND i.lft < :rgt
                AND e.type_id=:eventType
                AND (e.start_date IS NOT NULL OR e.end_date IS NOT NULL)';

        $params = [
            ':lft' => $io->lft,
            ':rgt' => $io->rgt,
            ':eventType' => $event->typeId,
        ];

        $eventData = QubitPdo::fetchOne($sql, $params, ['fetchMode' => PDO::FETCH_ASSOC]);

        // Update event with start and end dates if descendant events were found
        if ($eventData->found) {
            // Describe new dates
            $this->info($this->i18n->__(
                'Setting start date of event to %1 and end date to %2.',
                ['%1' => $this->describeDate($eventData->min), '%2' => $this->describeDate($eventData->max)]
            ));

            $event->startDate = $eventData->min;
            $event->endDate = $eventData->max;
            $event->save();
        }

        // Mark job as completed
        $this->info('Date calculation completed.');
        $this->job->setStatusCompleted();
        $this->job->save();

        return true;
    }

    private function describeDate($date)
    {
        return (null !== $date) ? $date : $this->i18n->__('[open ended]');
    }
}
