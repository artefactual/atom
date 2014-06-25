Information Object Assocations
==============================

The information object associations REST API endpoint provides data about
a specific information object assocation and allows updating/deletion of
assocations.

.. http:get:: /api/informationobjects/association/<id>

   Returns information object assocation data.

   **Example request**:

   .. sourcecode:: http

      GET /api/informationobjects/assocation/653 HTTP/1.1
      Host: example.com

   **Example response**:

   .. sourcecode:: http

      HTTP/1.1 200 OK
      Content-Type: application/json

    {
        "id": 653,
        "subject": {
            "id": 508,
            "title": "UXnfY6wiU4D6VNATtiNx"
        },
        "object": {
            "id": 506,
            "title": "bYyaVdquKHifXa85nEQb"
        },
        "type": {
            "id": 176,
            "name": "Related material descriptions"
        }
    }

   :statuscode 200: no error

.. http:put:: /api/informationobjects/assocation/1028

   Update information object assocation data.

   **Example request**:

   .. sourcecode:: http

      PUT /api/informationobjects/assocation/1028 HTTP/1.1
      Host: example.com

    {
        "type_id": 176,
        "description": "These are linked"
    }

   **Example response**:

   .. sourcecode:: http

      HTTP/1.1 200 OK
      Content-Type: application/json

   :statuscode 204: no content

.. http:delete:: /api/informationobjects/assocation/1028

   Delete information object association data.

   **Example request**:

   .. sourcecode:: http

      DELETE /api/informationobjects/assocation/1028 HTTP/1.1
      Host: example.com

   **Example response**:

   .. sourcecode:: http

      HTTP/1.1 200 OK
      Content-Type: application/json

    null

   :statuscode 204: no content
