Ingestion Summary
=================

The ingestion summary REST API endpoint lists how many of each type of AIP
were ingested.

.. http:get:: /api/summary/ingestion

   Statistics about ingestions.

   **Example request**:

   .. sourcecode:: http

      GET /api/summary/ingestion HTTP/1.1
      Host: example.com

   **Example response**:

   .. sourcecode:: http

      HTTP/1.1 200 OK
      Content-Type: application/json

    {
        "results": [{
            "total": 53,
            "type": "Artwork"
        }, {
            "total": 17,
            "type": "Supporting technology"
        }]
    }

   :statuscode 200: no error
