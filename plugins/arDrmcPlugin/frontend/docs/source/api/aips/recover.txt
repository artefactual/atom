Recover AIP
===========

The AIP recover REST API endpoint can be used to request an AIP be recovered
by the storage service.

The recovered AIP files must be placed in the designated recovery directory
accessible to the storage service. A storage service administrator will then
have to approve the recover request.

.. http:post:: /api/aips/<uuid>/recover

   Summary of API data.

   **Example request**:

   .. sourcecode:: http

      POST /api/aips/109a8155-9e0e-409c-bb60-b81aca351663/recover HTTP/1.1
      Host: example.com

   **Example response**:

   .. sourcecode:: http

      HTTP/1.1 200 OK
      Content-Type: text/javascript

    {
        "message": "Recover request created successfully."
    }

   :statuscode 200: no error
