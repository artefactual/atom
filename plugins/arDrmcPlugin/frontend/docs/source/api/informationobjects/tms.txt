Information Object TMS Data
===========================

The information objectsi TMS data REST API endpoint allows information object
TMS data to be browsed.

.. http:get:: /api/informationobjects/<id>/tms

   Summary of information object TMS data.

   **Example request**:

   .. sourcecode:: http

      GET /api/informationobjects/508/tms HTTP/1.1
      Host: example.com

   **Example response**:

   .. sourcecode:: http

      HTTP/1.1 200 OK
      Content-Type: application/json

    {
        "title": "UXnfY6wiU4D6VNATtiNx",
        "type": "Object"
    }

   :statuscode 200: no error
