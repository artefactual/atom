Ingestion Activity REST API
===========================

TODO: add example response

The ingestion activity REST API endpoint provides data about recent ingestions.

.. http:get:: /api/activity/ingestion

   Summary of ingestion activity.

   **Example request**:

   .. sourcecode:: http

      GET /api/activity/ingestion HTTP/1.1
      Host: example.com
      Accept: application/json, text/javascript

   **Example response**:

   .. sourcecode:: http

      HTTP/1.1 200 OK
      Content-Type: text/javascript

    {
    }

   :statuscode 200: no error
