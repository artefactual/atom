Information Object Associate
============================

The information object associate REST API endpoint allows the creation
of associations between information objects.

.. http:post:: /api/informationobjects/528/associate<id>

   Create an association between two information objects.

   **Example request**:

   .. sourcecode:: http

      POST /api/informationobjects/528/associate HTTP/1.1
      Host: example.com

   **Example response**:

   .. sourcecode:: http

      HTTP/1.1 200 OK
      Content-Type: application/json

    {
        "id": 653,
        "source_id": 508,
        "target_id": 506,
        "type_id": 176
    }

   :statuscode 200: no error
