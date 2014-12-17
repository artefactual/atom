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
 * Job scheduling settings
 *
 * @package    AccesstoMemory
 * @subpackage settings
 * @author     Peter Van Garderen <peter@artefactual.com>
 * @author     Jack Bates <jack@nottheoilrig.com>
 * @author     David Juhasz <david@artefactual.com>
 */

class SettingsJobAction extends sfAction
{
  public function execute($request)
  {
    $this->jobSchedulingForm = new SettingsJobSchedulingForm;

    // Handle POST data (form submit)
    if ($request->isMethod('post'))
    {
      QubitCache::getInstance()->removePattern('settings:i18n:*');

      // Handle job scheduling form submission
      if (null !== $request->job_scheduling)
      {
        $this->jobSchedulingForm->bind($request->job_scheduling);
        if ($this->jobSchedulingForm->isValid())
        {
          // Do update and redirect to avoid repeat submit wackiness
          $this->updateJobSchedulingSettings($this->jobSchedulingForm);
          $this->redirect('settings/job');
        }
      }
    }

    $this->populateJobSchedulingForm($this->jobSchedulingForm);
  }

  /**
   * Populate the Job scheduling form
   */
  protected function populateJobSchedulingForm()
  {
    $useJobScheduler = QubitSetting::getByName('use_job_scheduler');

    $this->jobSchedulingForm->setDefaults(array(
      'use_job_scheduler' => (isset($useJobScheduler)) ? intval($useJobScheduler->getValue(array('sourceCulture' => true))) : 1
    ));
  }

  /**
   * Update the Job scheduling settings
   */
  protected function updateJobSchedulingSettings()
  {
    $thisForm = $this->jobSchedulingForm;

    if (null !== $useJobSchedulerValue = $thisForm->getValue('use_job_scheduler'))
    {
      $setting = QubitSetting::getByName('use_job_scheduler');
      $setting->setValue($useJobSchedulerValue, array('sourceCulture' => true));
      $setting->save();
    }

    return $this;
  }
}
