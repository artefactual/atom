<?php

/**
 * arZoomPan Helper Functions.
 *
 * Helper functions for easy integration of zoom/pan viewer in templates
 *
 * @param mixed $digitalObject
 * @param mixed $options
 */

/**
 * Get zoom/pan viewer HTML.
 *
 * @param QubitDigitalObject $digitalObject The digital object to display
 * @param array              $options       Viewer options
 *
 * Modified by Johan Pieterse to use arMetadataExtractionPlugin
 * @return string HTML for the viewer
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
