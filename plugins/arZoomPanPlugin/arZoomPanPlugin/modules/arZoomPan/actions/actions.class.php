<?php

class arZoomPanActions extends sfActions
{
    /**
     * Viewer action (loads viewerSuccess.php)
     */
    public function executeViewer(sfWebRequest $request)
    {
        $id = $request->getParameter('id');

        if (!$id) {
            return $this->renderText("<h1>Error: No ID provided</h1>");
        }

        $this->digitalObject = QubitDigitalObject::getById($id);

        if (!$this->digitalObject) {
            return $this->renderText("<h1>Error: Digital Object not found (ID $id)</h1>");
        }

        // Debug log
        error_log("[ZoomPan] executeViewer loaded — ID=$id");

        return sfView::SUCCESS;
    }

    /**
     * Serve image tiles for OpenSeadragon
     */
    public function executeTile(sfWebRequest $request)
    {
        $id = $request->getParameter('id');
        $z  = $request->getParameter('z');
        $x  = $request->getParameter('x');
        $y  = $request->getParameter('y');
        $format = $request->getParameter('format', 'jpg');

        $digitalObject = QubitDigitalObject::getById($id);
        if (!$digitalObject) {
            $this->forward404('Digital object not found');
        }

        if (!QubitAcl::check($digitalObject->object, 'read')) {
            $this->forward403();
        }

        $tilePath = $this->getTilePath($digitalObject, $z, $x, $y, $format);

        if (!file_exists($tilePath)) {
            $this->generateTile($digitalObject, $z, $x, $y, $format, $tilePath);
        }

        $this->serveFile($tilePath, 'image/' . $format);
        return sfView::NONE;
    }


    /**
     * Return document information
     */
    public function executeInfo(sfWebRequest $request)
    {
        $id = $request->getParameter('id');
        $digitalObject = QubitDigitalObject::getById($id);

        if (!$digitalObject) {
            $this->forward404('Digital object not found');
        }

        if (!QubitAcl::check($digitalObject->object, 'read')) {
            $this->forward403();
        }

        $info = $this->getDocumentInfo($digitalObject);

        $this->getResponse()->setContentType('application/json');
        echo json_encode($info);

        return sfView::NONE;
    }


    /**
     * Render PDF page to image
     */
    public function executePdfPage(sfWebRequest $request)
    {
        $id   = $request->getParameter('id');
        $page = $request->getParameter('page', 1);

        $digitalObject = QubitDigitalObject::getById($id);
        if (!$digitalObject) {
            $this->forward404('Digital object not found');
        }

        if ($digitalObject->getMimeType() !== 'application/pdf') {
            $this->forward404('Not a PDF');
        }

        $imagePath = $this->convertPdfPage($digitalObject, $page);
        $this->serveFile($imagePath, 'image/jpeg');

        return sfView::NONE;
    }


    /**
     * Render text document as HTML
     */
    public function executeTextDocument(sfWebRequest $request)
    {
        $id = $request->getParameter('id');

        $digitalObject = QubitDigitalObject::getById($id);
        if (!$digitalObject) {
            $this->forward404('Digital object not found');
        }

        $mimeType = $digitalObject->getMimeType();
        $textTypes = array(
            'text/plain', 'text/html', 'text/xml', 'application/xml',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.oasis.opendocument.text'
        );

        if (!in_array($mimeType, $textTypes)) {
            $this->forward404('Not a text document');
        }

        $html = $this->convertToHtml($digitalObject);

        $this->getResponse()->setContentType('text/html');
        echo $html;

        return sfView::NONE;
    }


    /* ===========================================================
       Helper Methods (unchanged)
       =========================================================== */

    protected function generateTile($digitalObject, $z, $x, $y, $format, $tilePath)
    {
		$settings = sfConfig::get('app_zoom_pan_settings');
		if (!is_array($settings)) {
			$settings = array(
				'tile_size'      => 256,
				'max_zoom_level' => 12,
				'cache_directory'=> sfConfig::get('sf_root_dir') . '/cache/zoompan',
			);
		}
		$tileSize = $settings['tile_size'];

        $source = $digitalObject->getAbsolutePath(QubitDigitalObject::DERIVATIVE_TYPE_MASTER)
    ?: $digitalObject->getAbsolutePath(QubitDigitalObject::DERIVATIVE_TYPE_REFERENCE)
    ?: $digitalObject->getAbsolutePath();


        $scale = pow(2, $z);
        $x1 = $x * $tileSize;
        $y1 = $y * $tileSize;

        $cmd = sprintf(
            'convert "%s" -crop %dx%d+%d+%d -resize %dx%d "%s"',
            escapeshellarg($source),
            $tileSize / $scale, $tileSize / $scale,
            $x1 / $scale, $y1 / $scale,
            $tileSize, $tileSize,
            escapeshellarg($tilePath)
        );

        @mkdir(dirname($tilePath), 0755, true);
        exec($cmd);
    }


    protected function getTilePath($digitalObject, $z, $x, $y, $format)
    {
		$settings = sfConfig::get('app_zoom_pan_settings');
		if (!is_array($settings)) {
			$settings = array(
				'tile_size'      => 256,
				'max_zoom_level' => 12,
				'cache_directory'=> sfConfig::get('sf_root_dir') . '/cache/zoompan',
			);
		}
		$cache = $settings['cache_directory'];

		return sprintf(
			'%s/%d/%d/%d/%d.%s',
			$cache,
			$digitalObject->id,
			$z,
			$x,
			$y,
			$format
		);
    }


    protected function getDocumentInfo($digitalObject)
    {
        $path = $digitalObject->getAbsolutePath();
        $mime = $digitalObject->getMimeType();

        $info = array(
            'id'       => $digitalObject->id,
            'name'     => $digitalObject->getName(),
            'mimeType' => $mime,
            'fileSize' => filesize($path),
        );

        if (strpos($mime, 'image/') === 0) {
            $s    = getimagesize($path);
            $info['width']  = $s[0];
            $info['height'] = $s[1];
            $info['type']   = 'image';
        }

        return $info;
    }


    protected function convertPdfPage($digitalObject, $page)
    {
        $settings = sfConfig::get('app_zoom_pan_settings');
        $cache    = $settings['cache_directory'];

        $cachePath = sprintf('%s/pdf/%d/page_%d.jpg', $cache, $digitalObject->id, $page);

        @mkdir(dirname($cachePath), 0755, true);

        $cmd = sprintf(
            'convert -density 150 "%s[%d]" -quality 90 "%s"',
            escapeshellarg($digitalObject->getAbsolutePath()),
            $page - 1,
            escapeshellarg($cachePath)
        );

        exec($cmd);

        return $cachePath;
    }


    protected function convertToHtml($digitalObject)
    {
        return "<pre>Preview not implemented.</pre>";
    }


    protected function serveFile($path, $mime)
    {
        if (!file_exists($path)) {
            $this->forward404('File not found');
        }

        $response = $this->getResponse();
        $response->setContentType($mime);
        $response->setHttpHeader('Content-Length', filesize($path));

        readfile($path);
    }
}
