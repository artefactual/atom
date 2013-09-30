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

class RepositoryUploadLimitComponent extends sfComponent
{
  public function execute($request)
  {
    $this->resource = $request->getAttribute('sf_route')->resource;

    if (isset($this->resource) && 'QubitInformationObject' == get_class($this->resource))
    {
      $this->resource = $this->resource->getRepository(array('inherit' => true));
    }

    if (!isset($this->resource))
    {
      return sfView::NONE;
    }

    if (!$this->getUser()->isAuthenticated())
    {
      return sfView::NONE;
    }

    // Get upload type
    switch ($this->resource->uploadLimit)
    {
      case -1:
        $this->quotaType = 'unlimited';
        break;

      case 0:
        $this->quotaType = 'disabled';
        break;

      default:
        $this->quotaType = 'limited';
    }

    // Usage bar defaults
    $this->usageBarPixels = 0;
    $this->usageBarColors = array(
      'default' => '#390',
      'warning' => '#C33'
    );

    // Hide edit component for ajax response
    if (!isset($this->noedit))
    {
      $this->noedit = false;
    }

    // Calc disk usage display value (in GB)
    $this->diskUsage = $this->resource->getDiskUsage();
    if (0 != $this->diskUsage)
    {
      // Convert bytes to GB
      $this->diskUsage = floatval($this->diskUsage) / pow(10, 9);
    }

    // Get display value for upload limit
    $this->uploadLimit = $this->resource->uploadLimit;
    if (0 > $this->uploadLimit)
    {
      $this->uploadLimit = '<em>Unlimited</em>';
    }

    // Default color for "disk usage" bar is green
    $this->usageBarColor = $this->usageBarColors['default'];

    // Calc progress bar and percentages values for usage limit > 0
    if ('limited' == $this->quotaType)
    {
      // Calc percent
      $dup = $this->diskUsage / floatval($this->resource->uploadLimit) * 100;

      // Get display values
      if (0 <= $dup && 1 > $dup)
      {
        $this->diskUsagePercent = '<&nbsp;1';
        $this->usageBarPixels = '1';
      }
      else if (100 < $dup)
      {
        $this->diskUsagePercent = '>&nbsp;100';
        $this->usageBarPixels = 200;
        $this->usageBarColor = $this->usageBarColors['warning'];
      }
      else
      {
        $this->diskUsagePercent = round($dup, 0);
        $this->usageBarPixels = round($dup * 2, 0);
      }
    }

    // Get display value for disk usage
    if (0 < $this->diskUsage && 0.01 > $this->diskUsage)
    {
      $this->diskUsage = '<&nbsp;0.01';
    }
    else
    {
      $this->diskUsage = round($this->diskUsage, 2);
    }
  }
}
