<?php

class IiifManifestAction extends sfActions
{
  public function executeManifest(sfWebRequest $request)
  {
    $slug = $request->getParameter('slug');

    // Validate and sanitize slug
    if (empty($slug) || !is_string($slug)) {
      return $this->renderError('Invalid slug parameter', 400);
    }

    // Find object by slug
    $io = QubitInformationObject::getBySlug($slug);

    if (!$io) {
      return $this->renderError('Information object not found', 404);
    }

    // Security checks
    if (!QubitAcl::check($io, 'read')) {
      return $this->renderError('Access denied', 403);
    }

    // Check publication status for draft content
    $publicationStatusId = $io->getPublicationStatus()->statusId;
    if (QubitTerm::PUBLICATION_STATUS_DRAFT_ID == $publicationStatusId
        && !QubitAcl::check($io, 'viewDraft')) {
      return $this->renderError('Access denied', 403);
    }

    // Check if user can access reference images
    if (!QubitAcl::check($io, 'readReference')) {
      return $this->renderError('Access denied', 403);
    }

    // Check PREMIS rights for anonymous users
    if (!$this->getUser()->isAuthenticated()
        && !QubitGrantedRight::checkPremis($io->id, 'readReference')) {
      return $this->renderError('Access denied', 403);
    }

    $digitalObjects = $this->getDigitalObjects($io);
    // if (!is_array($digitalObjects)) {
    //   $digitalObjects[] = $digitalObjects;
    // }

    if (empty($digitalObjects)) {
      return $this->renderError('No digital objects found for this information object', 404);
    }

    // Determine the type of manifest to create based on digital object configuration
    $manifestType = $this->determineManifestType($digitalObjects);

    if ('none' === $manifestType) {
      return $this->renderError('No accessible digital objects found', 404);
    }

    try {
      // $manifest[] = $manifestType;
      // $manifest[] = ($io->getDigitalObject()) ? "direct do found" : "no direct do found";
      // $manifest[] = (count($digitalObjects) > 0) ? sprintf("digital objects found %s", count($digitalObjects))  : "no digital objects found";

      $manifest = $this->buildManifest($request, $io, $digitalObjects, $manifestType);

      $json = json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

      // Set caching headers
      $cacheTime = sfConfig::get('app_iiif_manifest_cache_ttl', 3600);
      if ($cacheTime > 0) {
        $this->getResponse()->setHttpHeader('Cache-Control', 'public, max-age='.$cacheTime);
        $this->getResponse()->setHttpHeader('ETag', md5($json));
      }
      $this->getResponse()->setContentType('application/json');

      return $this->renderText($json);
    } catch (Exception $e) {
      // Include detailed error information in debug mode
      $message = 'Error generating manifest';
      if (sfConfig::get('app_iiif_debug', false)) {
        $message .= ': '.$e->getMessage();
      }

      return $this->renderError($message, 500);
    }
  }

  private function getDigitalObjects(QubitInformationObject $io)
  {
    $criteria = new Criteria();
    $criteria->addJoin(QubitInformationObject::ID, QubitDigitalObject::OBJECT_ID);

    $criteria->add(
        QubitInformationObject::LFT,
        $io->lft,
        Criteria::GREATER_THAN
    );

    $criteria->add(
        QubitInformationObject::RGT,
        $io->rgt,
        Criteria::LESS_THAN
    );

    // Hide drafts
    $criteria = QubitAcl::addFilterDraftsCriteria($criteria);

    // Get linked digital objects if there are any.
    $digitalObject = $io->getDigitalObject();
    if ($digitalObject) {
      $accessibleDigitalObjects[] = $digitalObject;
    }

    // Get all digital objects related to this information object
    foreach (QubitDigitalObject::get($criteria) as $item) {
      $accessibleDigitalObjects[] = $item;
    }

    return $accessibleDigitalObjects;
  }

  private function renderError($message, $code)
  {
    $this->getResponse()->setStatusCode($code);
    $this->getResponse()->setContentType('application/json');

    return $this->renderText(json_encode(['error' => $message]));
  }

  private function determineManifestType($digitalObjects)
  {
    if (0 === count($digitalObjects)) {
      return 'none'; // No digital objects available
    }

    if (1 === count($digitalObjects)) {
      return 'single';
    }

    // compound - pdf pages?.

    return 'collection';
  }

  private function buildManifest($request, $io, $digitalObjects, $manifestType)
  {
    $protocol = $request->isSecure() ? 'https' : 'http';
    $host = $request->getHost();
    $manifestId = sprintf('%s://%s/iiif/manifest/%s', $protocol, $host, $io->slug);

    switch ($manifestType) {
      case 'single':
        return $this->buildSingleImageManifest($manifestId, $io, $digitalObjects);

      case 'collection':
        return $this->buildCollectionManifest($manifestId, $io, $digitalObjects);
      // case 'compound':
      //   return $this->buildCompoundManifest($manifestId, $io);

      default:
        throw new Exception('Unknown manifest type: '.$manifestType);
    }
  }

  private function buildSingleImageManifest($manifestId, $io, $digitalObjects)
  {
    $do = $digitalObjects[0];

    if (!isset($do)) {
      throw new Exception('No valid image found for single image manifest');
    }

    if (!QubitDigitalObject::isImageFile(basename($do->getAbsolutePath()))) {
      return null;
    }

    $manifest = [
        '@context' => 'http://iiif.io/api/presentation/2/context.json',
        '@type' => 'sc:Manifest',
        '@id' => $manifestId,
        'label' => $io->title ?: 'Untitled',
        'metadata' => $this->buildMetadata($io),
    ];

    // For single images, create one canvas
    $canvas = $this->buildCanvas($manifestId, $do, 1);
    if ($canvas) {
      $manifest['sequences'] = [[
          '@type' => 'sc:Sequence',
          'canvases' => [$canvas],
      ]];
    }

    // Add thumbnail if available
    // $thumbnail = $this->getThumbnailInfo($digitalObject);
    // if ($thumbnail) {
    //   $manifest['thumbnail'] = $thumbnail;
    // }

    return $manifest;
  }

  private function buildCollectionManifest($manifestId, $io, $digitalObjects)
  {
    // Filter to only image objects
    $imageObjects = [];
    foreach ($digitalObjects as $digitalObject) {
      if (QubitDigitalObject::isImageFile(basename($digitalObject->getAbsolutePath()))) {
        $imageObjects[] = $digitalObject;
      }
    }

    if (empty($imageObjects)) {
      throw new Exception('No valid images found for collection manifest');
    }

    $manifest = [
        '@context' => 'http://iiif.io/api/presentation/2/context.json',
        '@type' => 'sc:Manifest',
        '@id' => $manifestId,
        'label' => $io->title ?: 'Untitled',
        'metadata' => $this->buildMetadata($io),
    ];

    // Create canvases for each image
    $canvases = [];
    $canvasNumber = 1;

    foreach ($imageObjects as $digitalObject) {
      $canvas = $this->buildCanvas($manifestId, $digitalObject, $canvasNumber);
      if ($canvas) {
        $canvases[] = $canvas;
        ++$canvasNumber;
      }
    }

    if (!empty($canvases)) {
      $manifest['sequences'] = [[
          '@type' => 'sc:Sequence',
          'canvases' => $canvases,
      ]];
    }

    // Add thumbnail from first image
    // if (!empty($imageObjects)) {
    //   $thumbnail = $this->getThumbnailInfo($imageObjects[0]);
    //   if ($thumbnail) {
    //     $manifest['thumbnail'] = $thumbnail;
    //   }
    // }

    return $manifest;
  }

  // private function buildCompoundManifest($manifestId, $object)
  // {
  //   $digitalObject = $object->getDigitalObject();

  //   if (!$digitalObject) {
  //     throw new Exception('No digital object found for compound manifest');
  //   }

  //   $manifest = [
  //     '@context' => 'http://iiif.io/api/presentation/2/context.json',
  //     '@type' => 'sc:Manifest',
  //     '@id' => $manifestId,
  //     'label' => $object->title ?: 'Untitled',
  //     'metadata' => $this->buildMetadata($object),
  //   ];

  //   // Get all child objects for compound display
  //   $childObjects = $digitalObject->digitalObjectsRelatedByparentId;

  //   // If no children, treat parent as single canvas
  //   if (empty($childObjects)) {
  //     if ($this->isImageType($digitalObject)) {
  //       $canvas = $this->buildCanvas($manifestId, $digitalObject, 1);
  //       if ($canvas) {
  //         $manifest['sequences'] = [[
  //           '@type' => 'sc:Sequence',
  //           'canvases' => [$canvas],
  //         ]];
  //       }
  //     }
  //   } else {
  //     // Create canvases for each child (like pages in a book)
  //     $canvases = [];
  //     $canvasNumber = 1;

  //     foreach ($childObjects as $childObject) {
  //       if ($this->isImageType($childObject)) {
  //         $canvas = $this->buildCanvas($manifestId, $childObject, $canvasNumber);
  //         if ($canvas) {
  //           $canvases[] = $canvas;
  //           $canvasNumber++;
  //         }
  //       }
  //     }

  //     if (!empty($canvases)) {
  //       $manifest['sequences'] = [[
  //         '@type' => 'sc:Sequence',
  //         'canvases' => $canvases,
  //       ]];
  //     }
  //   }

  //   // Add thumbnail
  //   $thumbnail = $this->getThumbnailInfo($digitalObject);
  //   if ($thumbnail) {
  //     $manifest['thumbnail'] = $thumbnail;
  //   }

  //   return $manifest;
  // }

  private function buildCanvas($manifestId, $digitalObject, $canvasNumber)
  {
    // Skip non-image digital objects
    if (!QubitDigitalObject::isImageFile(basename($digitalObject->getAbsolutePath()))) {
      return null;
    }

    $canvasId = $manifestId.'/canvas/'.$canvasNumber;
    $filename = basename($digitalObject->name);

    // Sanitize filename to prevent path traversal
    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);

    $iiifServer = $this->getIiifServerUrl();
    $iiifBase = sprintf('%s/%s', $iiifServer, rawurlencode($filename));

    // Get actual image dimensions
    $dimensions = $this->getImageDimensions($digitalObject, $iiifBase);

    return [
        '@id' => $canvasId,
        '@type' => 'sc:Canvas',
        'label' => $digitalObject->name ?: 'Image '.$canvasNumber,
        'height' => $dimensions['height'],
        'width' => $dimensions['width'],
        'images' => [[
            '@type' => 'oa:Annotation',
            'motivation' => 'sc:painting',
            'resource' => [
                '@id' => $iiifBase.'/full/full/0/default.jpg',
                '@type' => 'dctypes:Image',
                'format' => $this->getOutputFormat($digitalObject),
                'height' => $dimensions['height'],
                'width' => $dimensions['width'],
            ],
            'on' => $canvasId,
        ]],
    ];
  }

  private function buildMetadata($object)
  {
    $metadata = [];

    // Only include metadata if feature is enabled
    if (!sfConfig::get('app_iiif_enable_metadata', true)) {
      return $metadata;
    }

    if ($object->scopeAndContent) {
      $metadata[] = ['label' => 'Description', 'value' => $object->scopeAndContent];
    }

    // if ($object->levelOfDescription) {
    //   $metadata[] = ['label' => 'Level of Description', 'value' => $object->levelOfDescription];
    // }

    if ($object->repository) {
      $metadata[] = ['label' => 'Repository', 'value' => $object->repository->authorizedFormOfName];
    }

    $do = $object->getDigitalObject();
    if ($do && $object->getDigitalObject()->mimeType) {
      $metadata[] = ['label' => 'Format', 'value' => $object->getDigitalObject()->mimeType];
    }

    return $metadata;
  }

  private function getImageDimensions($digitalObject, $iiifBase)
  {
    // Try to get dimensions from image file first
    $fullPath = $digitalObject->getFullPath();
    if (file_exists($fullPath) && is_readable($fullPath)) {
      $imageInfo = @getimagesize($fullPath);
      if (false !== $imageInfo) {
        return ['width' => $imageInfo[0], 'height' => $imageInfo[1]];
      }
    }

    // Fallback: try to get from IIIF server info.json
    // try {
    //   $infoUrl = $iiifBase.'/info.json';
    //   $context = stream_context_create([
    //       'http' => [
    //           'timeout' => 5,
    //           'method' => 'GET',
    //           'header' => 'Accept: application/json',
    //       ],
    //   ]);

    //   $response = @file_get_contents($infoUrl, false, $context);
    //   if (false !== $response) {
    //     $info = json_decode($response, true);
    //     if (isset($info['width'], $info['height'])) {
    //       return ['width' => $info['width'], 'height' => $info['height']];
    //     }
    //   }
    // } catch (Exception $e) {
    //   // Ignore errors and use fallback
    // }

    // // Final fallback: use configured default dimensions
    // return [
    //   'width' => sfConfig::get('app_iiif_default_width', 800),
    //   'height' => sfConfig::get('app_iiif_default_height', 600)
    // ];
  }

  // private function getThumbnailInfo($digitalObject)
  // {
  //   // Only include thumbnails if feature is enabled
  //   if (!sfConfig::get('app_iiif_enable_thumbnails', true)) {
  //     return null;
  //   }

  //   $thumbnail = $digitalObject->getChildByUsageId(QubitTerm::THUMBNAIL_ID);
  //   if ($thumbnail) {
  //     $iiifServer = $this->getIiifServerUrl();
  //     $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($thumbnail->path));

  //     return [
  //         '@id' => sprintf('%s/%s/full/!270,270/0/default.jpg', $iiifServer, rawurlencode($filename)),
  //         '@type' => 'dctypes:Image',
  //         'format' => 'image/jpeg',
  //     ];
  //   }

  //   return null;
  // }

  // private function isImageType($digitalObject)
  // {
  //   $imageTypes = [
  //       'image/jpeg', 'image/jpg', 'image/png', 'image/gif',
  //       'image/tiff', 'image/tif', 'image/bmp', 'image/webp',
  //   ];

  //   return in_array($digitalObject->mimeType, $imageTypes);
  // }

  private function getOutputFormat($digitalObject)
  {
    // IIIF servers typically output JPEG for web compatibility
    return 'image/jpeg';
  }

  private function getIiifServerUrl()
  {
    // Get IIIF server URL from configuration or environment
    $iiifServer = sfConfig::get('app_iiif_server_url');
    if (!$iiifServer) {
      // Fallback to environment variable
      $iiifServer = getenv('IIIF_SERVER_URL');
    }

    if (!$iiifServer) {
      // Final fallback (should be configured properly)
      $iiifServer = 'http://cantaloupe:8182/iiif/2';
    }

    return rtrim($iiifServer, '/');
  }
}
