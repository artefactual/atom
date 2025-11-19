<?php

/**
 * arZoomPan Helper Functions.
 *
 * Helper functions for easy integration of zoom/pan viewer in templates
 *
 * Get zoom/pan viewer HTML.
 *
 * @param QubitDigitalObject $digitalObject The digital object to display
 *
 * Modified by Johan Pieterse The Archive and Heritage Group <johan@theahg.co.za> to use arMetadataExtractionPlugin
 */

class arZoomPanViewerAction extends sfAction
{
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
