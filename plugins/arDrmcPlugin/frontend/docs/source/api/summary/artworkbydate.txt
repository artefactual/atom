Artwork by Date
===============

The artwork by date summary REST API endpoint provides counts (and running
totals) of how many artwork records were ingested, by month. Counts are
also provided by collection date.

.. http:get:: /api/summary/artworkbydate

   Monthly counts of ingested artwork.

   **Example request**:

   .. sourcecode:: http

      GET /api/summary/artworkbydate HTTP/1.1
      Host: example.com

   **Example response**:

   .. sourcecode:: http

      HTTP/1.1 200 OK
      Content-Type: application/json

    {
        "results": {
            "creation": [{
                "count": 53,
                "total": 53,
                "year": "2014",
                "month": "05"
            }],
            "collection": []
        }
    }

   :statuscode 200: no error
