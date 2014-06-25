Reclassifiy AIP
===============

The AIP reclassifiy REST API endpoint can be used to reclassify an AIP.

.. http:post:: /api/aips/<uuid>/reclassify

   Summary of API data.

   **Example request**:

   .. sourcecode:: http

      POST /api/aips/109a8155-9e0e-409c-bb60-b81aca351663/reclassify HTTP/1.1
      Host: example.com
      Accept: application/json, text/javascript

    {
        "type_id": 180
    }

   **Example response**:

   .. sourcecode:: http

      HTTP/1.1 200 OK
      Content-Type: text/javascript

    {
        "status": "Saved"
    }

   :statuscode 200: no error
