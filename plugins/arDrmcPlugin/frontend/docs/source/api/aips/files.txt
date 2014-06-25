AIP Files
=========

The AIP files REST API endpoint provides details about files in a specific AIP.

.. http:get:: /api/aips/<uuid>/files

   Summary of API data.

   **Example request**:

   .. sourcecode:: http

      GET /api/aips/109a8155-9e0e-409c-bb60-b81aca351663/files HTTP/1.1
      Host: example.com
      Accept: application/json, text/javascript

   **Example response**:

   .. sourcecode:: http

      HTTP/1.1 200 OK
      Content-Type: text/javascript

    {
        "results": [{
            "id": 646,
            "slug": "blastoff-wmv-3",
            "filename": "BlastOff.wmv",
            "mime_type": "video\/x-ms-asf",
            "byte_size": "1818411",
            "aip_uuid": "d9e5f49c-6d2a-4d01-8e6e-c1d602ff9229",
            "aip_title": "Cat_Pictures"
        }, {
            "id": 640,
            "slug": "0239-mpg-3",
            "filename": "0239.mpg",
            "byte_size": "4118728",
            "media_type_id": "138",
            "master_path": "http:\/\/10.0.2.15:8003\/uploads\/r\/null\/b\/7\/b794e283d7f6552717a7311f13bdbedc84945caca7b0203b212c71542a1beb1b\/03bb832b-c03b-45f9-82d3-d4f837ad59f4-0239.mpg",
            "aip_uuid": "d9e5f49c-6d2a-4d01-8e6e-c1d602ff9229",
            "aip_title": "Cat_Pictures"
        }, {
            "id": 648,
            "slug": "j6059-02-wma-3",
            "filename": "j6059_02.wma",
            "puid": "fmt\/132",
            "mime_type": "video\/x-ms-asf",
            "byte_size": "16418",
            "aip_uuid": "d9e5f49c-6d2a-4d01-8e6e-c1d602ff9229",
            "aip_title": "Cat_Pictures"
        }, {
            "id": 643,
            "slug": "bigteen-short1-mp3-3",
            "filename": "BigTeen_Short1.mp3",
            "puid": "fmt\/134",
            "byte_size": "647296",
            "media_type_id": "135",
            "master_path": "http:\/\/10.0.2.15:8003\/uploads\/r\/null\/5\/d\/5dc2e020455fdc154c02cb775fbd731ff58c776cf0034f268f7ee3dd7e15f5f2\/69f1cbcc-dc90-437b-8640-70745ed5dea6-BigTeen_Short1.mp3",
            "aip_uuid": "d9e5f49c-6d2a-4d01-8e6e-c1d602ff9229",
            "aip_title": "Cat_Pictures"
        }, {
            "id": 647,
            "slug": "funky-breakbeat-4-wav-3",
            "filename": "funky_breakbeat_4.wav",
            "byte_size": "333486",
            "aip_uuid": "d9e5f49c-6d2a-4d01-8e6e-c1d602ff9229",
            "aip_title": "Cat_Pictures"
        }, {
            "id": 649,
            "slug": "makeup-mov-3",
            "filename": "MakeUp.mov",
            "puid": "x-fmt\/384",
            "byte_size": "14955972",
            "aip_uuid": "d9e5f49c-6d2a-4d01-8e6e-c1d602ff9229",
            "aip_title": "Cat_Pictures"
        }, {
            "id": 650,
            "slug": "sample-aif-3",
            "filename": "sample.aif",
            "byte_size": "140834",
            "aip_uuid": "d9e5f49c-6d2a-4d01-8e6e-c1d602ff9229",
            "aip_title": "Cat_Pictures"
        }],
        "total": 7
    }

   :statuscode 200: no error
