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
 * arIiifPlugin components
 *
 * IIIF Image Carousel Plugin for AtoM
 * Modified by Johan Pieterse The Archive and Heritage Group <johan@theahg.co.za>
 */

class arIiifPluginComponents extends sfComponents
{
  /**
   * Display IIIF image carousel.
   *
   * @param mixed $request
   */
  public function executeCarousel($request)
  {
    if (!isset($this->resource)) {
      return sfView::NONE;
    }

    $this->images = [];

    if ($this->resource instanceof QubitDigitalObject) {
      $this->images = $this->getDigitalObjectIIIFImages($this->resource);
    } elseif ($this->resource instanceof QubitInformationObject) {
      $this->images = $this->getInformationObjectIIIFImages($this->resource);
    }

    if (empty($this->images)) {
      return sfView::NONE;
    }

    $config = sfConfig::get('app_iiif_carousel', [
        'auto_rotate' => true,
        'rotate_interval' => 5000,
        'show_navigation' => true,
        'show_thumbnails' => false,
        'viewer_height' => 600,
    ]);

    $this->autoRotate = isset($this->autoRotate) ? $this->autoRotate : $config['auto_rotate'];
    $this->rotateInterval = isset($this->rotateInterval) ? $this->rotateInterval : $config['rotate_interval'];
    $this->showNavigation = isset($this->showNavigation) ? $this->showNavigation : $config['show_navigation'];
    $this->showThumbnails = isset($this->showThumbnails) ? $this->showThumbnails : $config['show_thumbnails'];
    $this->viewerHeight = isset($this->viewerHeight) ? $this->viewerHeight : $config['viewer_height'];

    $this->carouselId = 'iiif-carousel-'.uniqid();
    $this->addAssets();
  }

  /**
   * Display simple IIIF viewer.
   *
   * @param mixed $request
   */
  public function executeViewer($request)
  {
    if (!isset($this->resource) || !$this->resource instanceof QubitDigitalObject) {
      return sfView::NONE;
    }

    $images = $this->getDigitalObjectIIIFImages($this->resource);

    if (empty($images)) {
      return sfView::NONE;
    }

    $this->iiifUrl = $images[0]['url'];
    $this->imageLabel = isset($images[0]['label']) ? $images[0]['label'] : '';

    $config = sfConfig::get('app_iiif_carousel', ['viewer_height' => 600]);
    $this->viewerHeight = isset($this->viewerHeight) ? $this->viewerHeight : $config['viewer_height'];
    $this->viewerId = 'iiif-viewer-'.uniqid();
    $this->addAssets();
  }

  /**
   * Get IIIF images from a digital object.
   *
   * @param mixed $digitalObject
   */
  protected function getDigitalObjectIIIFImages($digitalObject)
  {
    $images = [];

    // Check for IIIF manifest URL property using Criteria
    $criteria = new Criteria();
    $criteria->add(QubitProperty::OBJECT_ID, $digitalObject->id);
    $criteria->add(QubitProperty::NAME, 'iiifManifestUrl');

    foreach (QubitProperty::get($criteria) as $property) {
      $images[] = [
          'url' => $property->value,
          'label' => $digitalObject->name,
          'identifier' => $digitalObject->id,
      ];

      return $images;
    }

    // Construct IIIF URL from file path
    if (null !== $digitalObject->path) {
      $iiifBaseUrl = sfConfig::get('app_iiif_base_url');

      if (!empty($iiifBaseUrl)) {
        $identifier = $this->getIIIFIdentifier($digitalObject);

        $images[] = [
            'url' => rtrim($iiifBaseUrl, '/').'/'.$identifier.'/info.json',
            'label' => $digitalObject->name,
            'identifier' => $digitalObject->id,
            'path' => $digitalObject->path,
        ];
      }
    }

    return $images;
  }

  /**
   * Get IIIF images from an information object.
   *
   * @param mixed $informationObject
   */
  protected function getInformationObjectIIIFImages($informationObject)
  {
    $images = [];

    $criteria = new Criteria();
    $criteria->add(QubitDigitalObject::OBJECT_ID, $informationObject->id);
    $criteria->addAscendingOrderByColumn(QubitDigitalObject::SEQUENCE);

    foreach (QubitDigitalObject::get($criteria) as $digitalObject) {
      $digitalObjectImages = $this->getDigitalObjectIIIFImages($digitalObject);
      $images = array_merge($images, $digitalObjectImages);
    }

    return $images;
  }

  /**
   * Generate IIIF identifier from digital object.
   *
   * @param mixed $digitalObject
   */
  protected function getIIIFIdentifier($digitalObject)
  {
    if (null !== $digitalObject->checksum) {
      return $digitalObject->checksum;
    }

    $filename = pathinfo($digitalObject->path, PATHINFO_FILENAME);

    return preg_replace('/[^a-zA-Z0-9_-]/', '_', $filename);
  }

  /**
   * Add required CSS and JavaScript assets.
   */
  protected function addAssets()
  {
    $response = $this->getResponse();
    $response->addJavaScript('/plugins/arIiifPlugin/vendor/openseadragon/openseadragon.min.js', 'last');
    $response->addJavaScript('/plugins/arIiifPlugin/js/iiif-carousel.js', 'last');
    $response->addStylesheet('/plugins/arIiifPlugin/css/iiif-carousel.css', 'last');
  }
}
