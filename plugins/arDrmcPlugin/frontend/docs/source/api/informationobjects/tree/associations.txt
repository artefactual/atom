Information Object Tree Associations
====================================

TODO: example

The information object tree associations REST API endpoint provides data about
the associated associated with a specific information object tree.

.. http:get:: /api/informationobjects/<id>/tree/associations<id>

   Summary of information object data.

   **Example request**:

   .. sourcecode:: http

      GET /api/informationobjects/528/tree/associations HTTP/1.1
      Host: example.com

   **Example response**:

   .. sourcecode:: http

      HTTP/1.1 200 OK
      Content-Type: application/json

    []

   :statuscode 200: no error
