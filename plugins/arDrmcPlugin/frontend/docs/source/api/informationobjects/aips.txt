Information Object AIPs
=======================

The information objectsi AIPs REST API endpoint allows information object
AIPs data to be browsed.

.. http:get:: /api/informationobjects/<id>/aips

   Summary of information object AIP data.

   **Example request**:

   .. sourcecode:: http

      GET /api/informationobjects/508/aips HTTP/1.1
      Host: example.com

   **Example response**:

   .. sourcecode:: http

      HTTP/1.1 200 OK
      Content-Type: application/json

    {
        "results": null,
        "facets": null,
        "total": null,
        "overview": {
            "unclassified": {
                "count": 0
            },
            "total": {
                "size": 0,
                "count": 0
            }
        }
    }

   :statuscode 200: no error
