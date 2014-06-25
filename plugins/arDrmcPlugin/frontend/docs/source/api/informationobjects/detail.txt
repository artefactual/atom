Information Object Detail
=========================

The information object detail REST API endpoint provides data about
information objects that exist in the system and allows modification
and deletion.

.. http:get:: /api/informationobjects/<id>

   Summary of information object data.

   **Example request**:

   .. sourcecode:: http

      GET /api/informationobjects/528 HTTP/1.1
      Host: example.com

   **Example response**:

   .. sourcecode:: http

      HTTP/1.1 200 OK
      Content-Type: application/json

    {
        "id": 528,
        "level_of_description_id": 376,
        "parent_id": 527,
        "parent": "Play Dead; Real Time",
        "title": "MoMA 2012"
    }

   :statuscode 200: no error

.. http:put:: /api/informationobjects/<id>

   Update information object data.

   **Example request**:

   .. sourcecode:: http

      PUT /api/informationobjects/528 HTTP/1.1
      Host: example.com

    {
        "title": "Play Dead"
    }

   **Example response**:

   .. sourcecode:: http

      HTTP/1.1 200 OK
      Content-Type: application/json

    {
        "id":528,
        "parent_id":527
    }

   :statuscode 200: no error

.. http:delete:: /api/informationobjects/<id>

   Delete information object.

   **Example request**:

   .. sourcecode:: http

      DELETE /api/informationobjects/528 HTTP/1.1
      Host: example.com

   **Example response**:

   .. sourcecode:: http

      HTTP/1.1 200 OK
      Content-Type: application/json

    null

   :statuscode 200: no error
