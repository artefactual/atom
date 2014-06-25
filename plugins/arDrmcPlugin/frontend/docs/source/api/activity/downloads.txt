Download Activity REST API
==========================

The download activity REST API endpoint provides data about recent downloads.

.. http:get:: /api/activity/downloads

   Summary of download activity.

   **Example request**:

   .. sourcecode:: http

      GET /api/activity/downloads HTTP/1.1
      Host: example.com
      Accept: application/json, text/javascript

   **Example response**:

   .. sourcecode:: http

      HTTP/1.1 200 OK
      Content-Type: text/javascript

    {
        "results": [{
            "date": "2014-06-20 14:38:41",
            "username": "demo",
            "reason": "Reviewing file",
            "file": "0239.mpg"
        }, {
            "date": "2014-06-20 14:37:16",
            "username": "demo",
            "reason": "Comparing file to offline copy",
            "file": "cats.mpg"
        }]
    }

   :query limit: number of downloads to return
   :statuscode 200: no error
