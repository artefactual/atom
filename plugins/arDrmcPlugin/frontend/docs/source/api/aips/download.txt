AIP Download Proxy
==================

The AIP download REST API endpoint allows the downloading of specific AIP
or a file contained in an AIP.

Files are proxied from the storage service.

.. http:get:: /api/aips/<uuid>/download

   Returns file data.

   **Example request**:

   .. sourcecode:: http

      GET /api/aips/109a8155-9e0e-409c-bb60-b81aca351663/download HTTP/1.1
      Host: example.com

   :query file_id: ID of file to download (optional)
   :statuscode 200: no error
