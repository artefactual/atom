<?php

/**
 * IIIF module actions
 * 
 * @package    arIiifPlugin
 * @subpackage iiif
 * @author     Your Name
 */

class iiifActions extends sfActions
{
  /**
   * Generate IIIF Presentation API manifest for an information object
   *
   * @param sfWebRequest $request
   */
  public function executeManifest(sfWebRequest $request)
  {
    // Get information object by slug
    $this->resource = QubitInformationObject::getBySlug($request->slug);

    if (null === $this->resource)
    {
      $this->forward404();
    }

    // Check if manifests are enabled
    if (!sfConfig::get('app_iiif_enable_manifests', false))
    {
      $this->forward404();
    }

    // Get all digital objects for this information object
    $this->digitalObjects = $this->getDigitalObjects($this->resource);

    if (empty($this->digitalObjects))
    {
      $this->forward404();
    }

    // Set response content type
    $this->getResponse()->setContentType('application/json');
    $this->getResponse()->setHttpHeader('Access-Control-Allow-Origin', '*');

    // Build manifest
    $manifest = $this->buildManifest($this->resource, $this->digitalObjects);

    $this->getResponse()->setContent(json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
	return sfView::NONE;
  }

  /**
   * Generate IIIF manifest for a single digital object
   *
   * @param sfWebRequest $request
   */
  public function executeObjectManifest(sfWebRequest $request)
  {
    // Get digital object by ID
    $this->resource = QubitDigitalObject::getById($request->id);

    if (null === $this->resource)
    {
      $this->forward404();
    }

    // Check if manifests are enabled
    if (!sfConfig::get('app_iiif_enable_manifests', false))
    {
      $this->forward404();
    }

    // Set response content type
    $this->getResponse()->setContentType('application/json');
    $this->getResponse()->setHttpHeader('Access-Control-Allow-Origin', '*');

    // Build manifest for single object
    $manifest = $this->buildSingleObjectManifest($this->resource);

    $this->getResponse()->setContent(json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
	return sfView::NONE;
  }

  /**
   * Canvas endpoint
   *
   * @param sfWebRequest $request
   */
  public function executeCanvas(sfWebRequest $request)
  {
    $this->resource = QubitInformationObject::getBySlug($request->slug);

    if (null === $this->resource)
    {
      $this->forward404();
    }

    // Get canvas index
    $canvasIndex = (int)$request->canvas;

    // Get digital objects
    $digitalObjects = $this->getDigitalObjects($this->resource);

    if (!isset($digitalObjects[$canvasIndex]))
    {
      $this->forward404();
    }

    $this->getResponse()->setContentType('application/json');
    $this->getResponse()->setHttpHeader('Access-Control-Allow-Origin', '*');

    // Build canvas
    $canvas = $this->buildCanvas($digitalObjects[$canvasIndex], $canvasIndex);

    $this->getResponse()->setContent(json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
	return sfView::NONE;
  }

  /**
   * Get digital objects for an information object
   *
   * @param QubitInformationObject $resource
   * @return array
   */
  protected function getDigitalObjects($resource)
  {
    $digitalObjects = array();

    $criteria = new Criteria;
    $criteria->add(QubitDigitalObject::OBJECT_ID, $resource->id);
    $criteria->addAscendingOrderByColumn(QubitDigitalObject::SEQUENCE);

    foreach (QubitDigitalObject::get($criteria) as $digitalObject)
    {
      // Check if this object has a valid path or IIIF URL
      if (null !== $digitalObject->path || $this->hasIIIFUrl($digitalObject))
      {
        $digitalObjects[] = $digitalObject;
      }
    }

    return $digitalObjects;
  }

  /**
   * Check if digital object has IIIF URL
   *
   * @param QubitDigitalObject $digitalObject
   * @return boolean
   */
  protected function hasIIIFUrl($digitalObject)
  {
    $criteria = new Criteria;
    $criteria->add(QubitProperty::OBJECT_ID, $digitalObject->id);
    $criteria->add(QubitProperty::NAME, 'iiifManifestUrl');
    
    return QubitProperty::get($criteria)->count() > 0;
  }

  /**
   * Build IIIF Presentation API 2.1 manifest
   *
   * @param QubitInformationObject $resource
   * @param array $digitalObjects
   * @return array
   */
  protected function buildManifest($resource, $digitalObjects)
  {
    $baseUrl = $this->getContext()->getRequest()->getUriPrefix() . 
               $this->getContext()->getRequest()->getRelativeUrlRoot();

    $manifestUrl = $baseUrl . '/iiif/' . $resource->slug . '/manifest';
    $iiifBaseUrl = sfConfig::get('app_iiif_base_url');

    $manifest = array(
      '@context' => 'http://iiif.io/api/presentation/2/context.json',
      '@id' => $manifestUrl,
      '@type' => 'sc:Manifest',
      'label' => $resource->getTitle(array('cultureFallback' => true)),
      'metadata' => $this->buildMetadata($resource),
      'description' => strip_tags($resource->getScopeAndContent(array('cultureFallback' => true))),
      'attribution' => $resource->getRepository(array('cultureFallback' => true)),
      'logo' => $baseUrl . '/uploads/r/repository/logo/logo.png',
      'sequences' => array(
        array(
          '@type' => 'sc:Sequence',
          'label' => 'Image sequence',
          'canvases' => array()
        )
      )
    );

    // Build canvases
    foreach ($digitalObjects as $index => $digitalObject)
    {
      $manifest['sequences'][0]['canvases'][] = $this->buildCanvas($digitalObject, $index);
    }

    return $manifest;
  }

  /**
   * Build manifest for single digital object
   *
   * @param QubitDigitalObject $digitalObject
   * @return array
   */
  protected function buildSingleObjectManifest($digitalObject)
  {
    $baseUrl = $this->getContext()->getRequest()->getUriPrefix() . 
               $this->getContext()->getRequest()->getRelativeUrlRoot();

    $manifestUrl = $baseUrl . '/iiif/object/' . $digitalObject->id . '/manifest';

    $manifest = array(
      '@context' => 'http://iiif.io/api/presentation/2/context.json',
      '@id' => $manifestUrl,
      '@type' => 'sc:Manifest',
      'label' => $digitalObject->name,
      'sequences' => array(
        array(
          '@type' => 'sc:Sequence',
          'canvases' => array(
            $this->buildCanvas($digitalObject, 0)
          )
        )
      )
    );

    return $manifest;
  }

  /**
   * Build canvas for a digital object
   *
   * @param QubitDigitalObject $digitalObject
   * @param int $index
   * @return array
   */
  protected function buildCanvas($digitalObject, $index)
  {
    $baseUrl = $this->getContext()->getRequest()->getUriPrefix() . 
               $this->getContext()->getRequest()->getRelativeUrlRoot();
    
    $iiifBaseUrl = sfConfig::get('app_iiif_base_url');
    
    // Get IIIF identifier
    $identifier = $this->getIIIFIdentifier($digitalObject);
    $imageUrl = rtrim($iiifBaseUrl, '/') . '/' . $identifier;

    // Get image dimensions (you may need to adjust this based on your setup)
    $width = 1000;  // Default width
    $height = 1000; // Default height

    // Try to get actual dimensions if available
    if (file_exists($digitalObject->getAbsolutePath()))
    {
      $imageSize = @getimagesize($digitalObject->getAbsolutePath());
      if ($imageSize !== false)
      {
        $width = $imageSize[0];
        $height = $imageSize[1];
      }
    }

    $canvas = array(
      '@id' => $baseUrl . '/iiif/canvas/' . $digitalObject->id,
      '@type' => 'sc:Canvas',
      'label' => $digitalObject->name ?: 'Image ' . ($index + 1),
      'width' => $width,
      'height' => $height,
      'images' => array(
        array(
          '@type' => 'oa:Annotation',
          'motivation' => 'sc:painting',
          'resource' => array(
            '@id' => $imageUrl . '/full/full/0/default.jpg',
            '@type' => 'dctypes:Image',
            'format' => 'image/jpeg',
            'width' => $width,
            'height' => $height,
            'service' => array(
              '@context' => 'http://iiif.io/api/image/2/context.json',
              '@id' => $imageUrl,
              'profile' => 'http://iiif.io/api/image/2/level2.json'
            )
          ),
          'on' => $baseUrl . '/iiif/canvas/' . $digitalObject->id
        )
      )
    );

    return $canvas;
  }

  /**
   * Build metadata array
   *
   * @param QubitInformationObject $resource
   * @return array
   */
  protected function buildMetadata($resource)
  {
    $metadata = array();

    // Add reference code
    if ($refCode = $resource->referenceCode)
    {
      $metadata[] = array(
        'label' => 'Reference code',
        'value' => $refCode
      );
    }

    // Add creation date
    if ($dates = $resource->getDates())
    {
      foreach ($dates as $date)
      {
        $metadata[] = array(
          'label' => 'Date',
          'value' => $date->getDate(array('cultureFallback' => true))
        );
      }
    }

    // Add level of description
    if ($levelOfDescription = $resource->getLevelOfDescription())
    {
      $metadata[] = array(
        'label' => 'Level of description',
        'value' => $levelOfDescription->__toString()
      );
    }

    return $metadata;
  }

  /**
   * Generate IIIF identifier from digital object
   *
   * @param QubitDigitalObject $digitalObject
   * @return string
   */
  protected function getIIIFIdentifier($digitalObject)
  {
    $criteria = new Criteria;
    $criteria->add(QubitProperty::OBJECT_ID, $digitalObject->id);
    $criteria->add(QubitProperty::NAME, 'iiifIdentifier');
    
    foreach (QubitProperty::get($criteria) as $property)
    {
      return $property->value;
    }

    if (null !== $digitalObject->checksum)
    {
      return $digitalObject->checksum;
    }
    
    $filename = pathinfo($digitalObject->path, PATHINFO_FILENAME);
    return preg_replace('/[^a-zA-Z0-9_-]/', '_', $filename);
  }
}
