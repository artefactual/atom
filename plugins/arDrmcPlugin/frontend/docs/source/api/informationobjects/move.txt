Information Object Move
=======================

The information object move REST API endpoint allows information objects to
be moved.

.. http:post:: /api/informationobjects/<id>/move

   Summary of information object data.

   **Example request**:

   .. sourcecode:: http

      POST /api/informationobjects/528/move HTTP/1.1
      Host: example.com

    {
        "parent_id": 234
    }

   **Example response**:

   .. sourcecode:: http

      HTTP/1.1 200 OK
      Content-Type: application/json

    {
      "id": 528,
      "parent_id": 234
    }

   :statuscode 200: no error
