<?php

/**
 * arZoomPan Viewer Action.
 *
 * Handles the viewer action for zoom/pan functionality with digital objects
 *
 * Modified by Johan Pieterse The Archive and Heritage Group <johan@theahg.co.za> to use arMetadataExtractionPlugin
 */
class arZoomPanViewerAction extends sfAction
{
    /**
     * Execute the viewer action.
     *
     * @param sfWebRequest $request
     *
     * @return string
     */
    public function execute($request)
    {
        $this->digitalObject = QubitDigitalObject::getById($request->getParameter('id'));
        if (!$this->digitalObject) {
            return $this->renderText('<h1>ZoomPan Error: DigitalObject not found</h1>');
        }

        // DEBUG
        // error_log("[ZoomPan] ViewerAction loaded for ID: " . $this->digitalObject->id);

        return sfView::SUCCESS;
    }
}