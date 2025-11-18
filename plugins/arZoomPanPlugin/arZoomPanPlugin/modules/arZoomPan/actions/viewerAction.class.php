<?php

class arZoomPanViewerAction extends sfAction
{
    public function execute($request)
    {
        $this->digitalObject = QubitDigitalObject::getById($request->getParameter('id'));

        if (!$this->digitalObject) {
            return $this->renderText("<h1>ZoomPan Error: DigitalObject not found</h1>");
        }

        // DEBUG
        //error_log("[ZoomPan] ViewerAction loaded for ID: " . $this->digitalObject->id);

        return sfView::SUCCESS;
    }
}
