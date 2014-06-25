Information Object Tree
=======================

The information object tree REST API endpoint provides data about the
hierarchy associated with a specific information object.

.. http:get:: /api/informationobjects/<id>/tree<id>

   Summary of information object data.

   **Example request**:

   .. sourcecode:: http

      GET /api/informationobjects/528/tree HTTP/1.1
      Host: example.com

   **Example response**:

   .. sourcecode:: http

      HTTP/1.1 200 OK
      Content-Type: application/json

    {
        "id": 508,
        "level_of_description_id": 375,
        "title": "UXnfY6wiU4D6VNATtiNx",
        "supporting_technologies_count": 0
    }

   :statuscode 200: no error
