<?php

/**
 * arIiifPlugin components
 * 
 * @package    arIiifPlugin
 * @subpackage iiif
 */

class arIiifPluginComponents extends sfComponents
{
  /**
   * Display IIIF image carousel
   */
  public function executeCarousel($request)
  {
    if (!isset($this->resource))
    {
      return sfView::NONE;
    }

    $this->images = array();
    
    if ($this->resource instanceof QubitDigitalObject)
    {
      $this->images = $this->getDigitalObjectIIIFImages($this->resource);
    }
    else if ($this->resource instanceof QubitInformationObject)
    {
      $this->images = $this->getInformationObjectIIIFImages($this->resource);
    }

    if (empty($this->images))
    {
      return sfView::NONE;
    }

    $config = sfConfig::get('app_iiif_carousel', array(
      'auto_rotate' => true,
      'rotate_interval' => 5000,
      'show_navigation' => true,
      'show_thumbnails' => false,
      'viewer_height' => 600
    ));

    $this->autoRotate = isset($this->autoRotate) ? $this->autoRotate : $config['auto_rotate'];
    $this->rotateInterval = isset($this->rotateInterval) ? $this->rotateInterval : $config['rotate_interval'];
    $this->showNavigation = isset($this->showNavigation) ? $this->showNavigation : $config['show_navigation'];
    $this->showThumbnails = isset($this->showThumbnails) ? $this->showThumbnails : $config['show_thumbnails'];
    $this->viewerHeight = isset($this->viewerHeight) ? $this->viewerHeight : $config['viewer_height'];

    $this->carouselId = 'iiif-carousel-' . uniqid();
    $this->addAssets();
  }

  /**
   * Display simple IIIF viewer
   */
  public function executeViewer($request)
  {
    if (!isset($this->resource) || !$this->resource instanceof QubitDigitalObject)
    {
      return sfView::NONE;
    }

    $images = $this->getDigitalObjectIIIFImages($this->resource);
    
    if (empty($images))
    {
      return sfView::NONE;
    }

    $this->iiifUrl = $images[0]['url'];
    $this->imageLabel = isset($images[0]['label']) ? $images[0]['label'] : '';

    $config = sfConfig::get('app_iiif_carousel', array('viewer_height' => 600));
    $this->viewerHeight = isset($this->viewerHeight) ? $this->viewerHeight : $config['viewer_height'];
    $this->viewerId = 'iiif-viewer-' . uniqid();
    $this->addAssets();
  }

  /**
   * Get IIIF images from a digital object
   */
  protected function getDigitalObjectIIIFImages($digitalObject)
  {
    $images = array();

    // Check for IIIF manifest URL property using Criteria
    $criteria = new Criteria;
    $criteria->add(QubitProperty::OBJECT_ID, $digitalObject->id);
    $criteria->add(QubitProperty::NAME, 'iiifManifestUrl');
    
    foreach (QubitProperty::get($criteria) as $property)
    {
      $images[] = array(
        'url' => $property->value,
        'label' => $digitalObject->name,
        'identifier' => $digitalObject->id
      );
      
      return $images;
    }

    // Construct IIIF URL from file path
    if (null !== $digitalObject->path)
    {
      $iiifBaseUrl = sfConfig::get('app_iiif_base_url');
      
      if (!empty($iiifBaseUrl))
      {
        $identifier = $this->getIIIFIdentifier($digitalObject);
        
        $images[] = array(
          'url' => rtrim($iiifBaseUrl, '/') . '/' . $identifier . '/info.json',
          'label' => $digitalObject->name,
          'identifier' => $digitalObject->id,
          'path' => $digitalObject->path
        );
      }
    }

    return $images;
  }

  /**
   * Get IIIF images from an information object
   */
  protected function getInformationObjectIIIFImages($informationObject)
  {
    $images = array();

    $criteria = new Criteria;
    $criteria->add(QubitDigitalObject::OBJECT_ID, $informationObject->id);
    $criteria->addAscendingOrderByColumn(QubitDigitalObject::SEQUENCE);

    foreach (QubitDigitalObject::get($criteria) as $digitalObject)
    {
      $digitalObjectImages = $this->getDigitalObjectIIIFImages($digitalObject);
      $images = array_merge($images, $digitalObjectImages);
    }

    return $images;
  }

  /**
   * Generate IIIF identifier from digital object
   */
  protected function getIIIFIdentifier($digitalObject)
  {
    if (null !== $digitalObject->checksum)
    {
      return $digitalObject->checksum;
    }
    
    $filename = pathinfo($digitalObject->path, PATHINFO_FILENAME);
    $identifier = preg_replace('/[^a-zA-Z0-9_-]/', '_', $filename);
    
    return $identifier;
  }

  /**
   * Add required CSS and JavaScript assets
   */
  protected function addAssets()
  {
    $response = $this->getResponse();
    $response->addJavaScript('/plugins/arIiifPlugin/vendor/openseadragon/openseadragon.min.js', 'last');
    $response->addJavaScript('/plugins/arIiifPlugin/js/iiif-carousel.js', 'last');
    $response->addStylesheet('/plugins/arIiifPlugin/css/iiif-carousel.css', 'last');
  }
}
