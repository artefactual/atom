AIPs Storage Service Status
===========================

The AIPs storage service status REST API endpoint provides a list of AIP UUIDs
for AIPs that have a specified storage service status.

.. http:get:: /api/aips/status

   Summary of API data.

   **Example request**:

   .. sourcecode:: http

      GET /api/aips/status?status=RECOVER_REQ HTTP/1.1
      Host: example.com
      Accept: application/json, text/javascript

   **Example response**:

   .. sourcecode:: http

      HTTP/1.1 200 OK
      Content-Type: text/javascript

      {
          "uuids": [
              "1b43ab7a-983b-47b3-953f-f026fa090490"
          ]
      }

   :query status: storage service status
   :statuscode 200: no error
