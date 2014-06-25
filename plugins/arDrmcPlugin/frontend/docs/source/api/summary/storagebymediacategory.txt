Storage by Media Category
=========================

The storage by media category summary REST API endpoint lists how much storage
is used per MIME type.

.. http:get:: /api/summary/storagebymediacategory

   Storage used per MIME type.

   **Example request**:

   .. sourcecode:: http

      GET /api/summary/storagebymediacategory HTTP/1.1
      Host: example.com
      Accept: application/json, text/javascript

   **Example response**:

   .. sourcecode:: http

      HTTP/1.1 200 OK
      Content-Type: application/json

    {
        "results": [{
            "total": 3081552,
            "media_type": "audio\/mpeg"
        }, {
            "total": 12356184,
            "media_type": "video\/mpeg"
        }, {
            "total": 51554,
            "media_type": "image\/png"
        }, {
            "total": 6071934,
            "media_type": "video\/mp4"
        }]
    }

   :statuscode 200: no error
