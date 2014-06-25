Information Object Files
========================

The information objectsi files REST API endpoint allows information object
files data to be browsed.

.. http:get:: /api/informationobjects/<id>/files

   Summary of information object files data.

   **Example request**:

   .. sourcecode:: http

      GET /api/informationobjects/508/files HTTP/1.1
      Host: example.com

   **Example response**:

   .. sourcecode:: http

      HTTP/1.1 200 OK
      Content-Type: application/json

    {
        "results": [],
        "total":0
    }

   :statuscode 200: no error
