Storage by Codec
================

The storage by codec summary REST API endpoint provides stats about how much
storage each codec is using.

.. http:get:: /api/summary/storagebycodec

   Statistics about file storage used by various codecs.

   **Example request**:

   .. sourcecode:: http

      GET /api/summary/storagebycodec HTTP/1.1
      Host: example.com

   **Example response**:

   .. sourcecode:: http

      HTTP/1.1 200 OK
      Content-Type: application/json

    {
        "results": [{
            "total": 10180006,
            "codec": "WAVE"
        }, {
            "total": 10180006,
            "codec": "QUICKTIME"
        }, {
            "total": 10180006,
            "codec": "MPEG VIDEO"
        }, {
            "total": 10180006,
            "codec": "MPEG AUDIO"
        }, {
            "total": 10180006,
            "codec": "AIFF"
        }, {
            "total": 10180006,
            "codec": "JPEG"
        }, {
            "total": 10180006,
            "codec": "MPEG-1V"
        }, {
            "total": 10180006,
            "codec": "PCM"
        }, {
            "total": 10180006,
            "codec": "MPA1L3"
        }]
    }

   :statuscode 200: no error
