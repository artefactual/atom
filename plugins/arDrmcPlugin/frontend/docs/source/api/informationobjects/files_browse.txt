Information Object Files Browse
===============================

The information objectsi files browse REST API endpoint allows information
object files data to be browsed.

.. http:get:: /api/informationobjects/files

   Summary of information object files data.

   **Example request**:

   .. sourcecode:: http

      GET /api/informationobjects/files HTTP/1.1
      Host: example.com

   **Example response**:

   .. sourcecode:: http

      HTTP/1.1 200 OK
      Content-Type: application/json

    {
        "results": {
            "530": {
                "id": 530,
                "filename": "Picture I",
                "slug": "picture-i",
                "media_type_id": "136",
                "byte_size": "16942",
                "mime_type": "image\/png",
                "thumbnail_path": "http:\/\/10.0.2.15:8003\/uploads\/r\/null\/2\/2\/2225caeda4d3421d447809b98f15ce48d13062049c857adf6f572689ba964449\/pic-1_142.jpg",
                "master_path": "http:\/\/10.0.2.15:8003\/uploads\/r\/null\/f\/d\/fdf08786cb6a21dd2e9d4fee9868ebf22ca0bd0174db51626ce340e57a2eac21\/pic-1.png",
                "media_type": "Image"
            },
            "534": {
                "id": 534,
                "filename": "Picture II",
                "slug": "picture-ii",
                "media_type_id": "136",
                "byte_size": "17296",
                "mime_type": "image\/png",
                "thumbnail_path": "http:\/\/10.0.2.15:8003\/uploads\/r\/null\/1\/c\/1cc5e8dfe914cb5a071387f2cd669f4b9a147adeba255ccc69ea387b3bf57efb\/pic-2_142.jpg",
                "master_path": "http:\/\/10.0.2.15:8003\/uploads\/r\/null\/f\/b\/fbb049101bf7cadb1990f53c0e1a116b0c6385a0121ce47e616327a4e8e8ec7d\/pic-2.png",
                "media_type": "Image"
            },
            "538": {
                "id": 538,
                "filename": "Picture III",
                "slug": "picture-iii",
                "media_type_id": "136",
                "byte_size": "17316",
                "mime_type": "image\/png",
                "thumbnail_path": "http:\/\/10.0.2.15:8003\/uploads\/r\/null\/9\/7\/97b75215465838a590b898ae2b3961f605f72f444a0f0be505792610815d41b0\/pic-3_142.jpg",
                "master_path": "http:\/\/10.0.2.15:8003\/uploads\/r\/null\/8\/e\/8eeb1be71daf22b5f71f5151622b9ec10c8c191eed4fc77cbf51d2c86b36958e\/pic-3.png",
                "media_type": "Image"
            },
            "570": {
                "id": 570,
                "filename": "j6059_02.wma",
                "slug": "j6059-02-wma",
                "media_type_id": "135",
                "byte_size": "37033",
                "size_in_aip": "16418",
                "date_ingested": "2014-06-19T22:38:59Z",
                "mime_type": "audio\/mpeg",
                "thumbnail_path": "http:\/\/10.0.2.15:8003\/images\/.png",
                "master_path": "http:\/\/10.0.2.15:8003\/uploads\/r\/null\/f\/6\/f68c265ff0f44a6d43cdc055fe44c4bc9c6f3cd989625e6fffecafbe24cb8e95\/b2aba46f-8e50-4713-b6bb-dcdb924458cb-j6059_02.mp3",
                "media_type": "Audio",
                "original_relative_path_within_aip": "objects\/j6059_02.wma"
            },
            "633": {
                "id": 633,
                "filename": "funky_breakbeat_4.wav",
                "slug": "funky-breakbeat-4-wav-2",
                "size_in_aip": "333486",
                "date_ingested": "2014-06-24T06:20:34Z",
                "thumbnail_path": "http:\/\/10.0.2.15:8003\/images\/.png",
                "master_path": "http:\/\/10.0.2.15:8003\/images\/.png",
                "aip_uuid": "12e4d9eb-b00f-464d-914e-4d6c7b0fe8b6",
                "aip_title": "ABBBBA"
            },
            "646": {
                "id": 646,
                "filename": "BlastOff.wmv",
                "slug": "blastoff-wmv-3",
                "size_in_aip": "1818411",
                "date_ingested": "2014-06-24T06:41:34Z",
                "thumbnail_path": "http:\/\/10.0.2.15:8003\/images\/.png",
                "master_path": "http:\/\/10.0.2.15:8003\/images\/.png",
                "aip_uuid": "d9e5f49c-6d2a-4d01-8e6e-c1d602ff9229",
                "aip_title": "Cat_Pictures"
            },
            "566": {
                "id": 566,
                "filename": "BlastOff.wmv",
                "slug": "blastoff-wmv",
                "media_type_id": "138",
                "byte_size": "2459959",
                "size_in_aip": "1818411",
                "date_ingested": "2014-06-19T22:38:59Z",
                "mime_type": "video\/mp4",
                "thumbnail_path": "http:\/\/10.0.2.15:8003\/uploads\/r\/null\/b\/1\/b12778c40641639d11b34f191fea48b7c0966be769356dcb6b3b2a201faf4e95\/4f817f26-ca81-4b08-991f-1fdef26ece9b-BlastOff_142.jpg",
                "master_path": "http:\/\/10.0.2.15:8003\/uploads\/r\/null\/b\/1\/b12778c40641639d11b34f191fea48b7c0966be769356dcb6b3b2a201faf4e95\/4f817f26-ca81-4b08-991f-1fdef26ece9b-BlastOff.mp4",
                "media_type": "Video",
                "original_relative_path_within_aip": "objects\/BlastOff.wmv"
            },
            "579": {
                "id": 579,
                "filename": "0239.mpg",
                "slug": "0239-mpg",
                "media_type_id": "138",
                "byte_size": "4118728",
                "size_in_aip": "4118728",
                "date_ingested": "2014-06-19T22:39:00Z",
                "mime_type": "video\/mpeg",
                "thumbnail_path": "http:\/\/10.0.2.15:8003\/images\/.png",
                "master_path": "http:\/\/10.0.2.15:8003\/uploads\/r\/null\/b\/7\/b794e283d7f6552717a7311f13bdbedc84945caca7b0203b212c71542a1beb1b\/7e13016c-4452-4414-ba52-8ac9e4011e86-0239.mpg",
                "media_type": "Video",
                "original_relative_path_within_aip": "objects\/0239.mpg"
            },
            "629": {
                "id": 629,
                "filename": "BigTeen_Short1.mp3",
                "slug": "bigteen-short1-mp3-2",
                "media_type_id": "135",
                "byte_size": "971275",
                "size_in_aip": "647296",
                "date_ingested": "2014-06-24T06:20:34Z",
                "mime_type": "audio\/mpeg",
                "thumbnail_path": "http:\/\/10.0.2.15:8003\/images\/.png",
                "master_path": "http:\/\/10.0.2.15:8003\/uploads\/r\/null\/5\/d\/5dc2e020455fdc154c02cb775fbd731ff58c776cf0034f268f7ee3dd7e15f5f2\/7addb555-0bda-43cc-b4d1-5dcadbd12231-BigTeen_Short1.mp3",
                "media_type": "Audio",
                "aip_uuid": "12e4d9eb-b00f-464d-914e-4d6c7b0fe8b6",
                "aip_title": "ABBBBA"
            },
            "634": {
                "id": 634,
                "filename": "j6059_02.wma",
                "slug": "j6059-02-wma-2",
                "size_in_aip": "16418",
                "date_ingested": "2014-06-24T06:20:34Z",
                "thumbnail_path": "http:\/\/10.0.2.15:8003\/images\/.png",
                "master_path": "http:\/\/10.0.2.15:8003\/images\/.png",
                "aip_uuid": "12e4d9eb-b00f-464d-914e-4d6c7b0fe8b6",
                "aip_title": "ABBBBA"
            }
        },
        "facets": {
            "format": {
                "_type": "terms",
                "missing": 9,
                "total": 15,
                "other": 0,
                "terms": [{
                    "term": "Wave",
                    "count": 3,
                    "label": "Wave"
                }, {
                    "term": "QuickTime",
                    "count": 3,
                    "label": "QuickTime"
                }, {
                    "term": "MPEG Video",
                    "count": 3,
                    "label": "MPEG Video"
                }, {
                    "term": "MPEG Audio",
                    "count": 3,
                    "label": "MPEG Audio"
                }, {
                    "term": "AIFF",
                    "count": 3,
                    "label": "AIFF"
                }]
            },
            "videoCodec": {
                "_type": "terms",
                "missing": 18,
                "total": 6,
                "other": 0,
                "terms": [{
                    "term": "jpeg",
                    "count": 3,
                    "label": "jpeg"
                }, {
                    "term": "MPEG-1V",
                    "count": 3,
                    "label": "MPEG-1V"
                }]
            },
            "audioCodec": {
                "_type": "terms",
                "missing": 12,
                "total": 12,
                "other": 0,
                "terms": [{
                    "term": "PCM",
                    "count": 9,
                    "label": "PCM"
                }, {
                    "term": "MPA1L3",
                    "count": 3,
                    "label": "MPA1L3"
                }]
            },
            "resolution": {
                "_type": "terms",
                "missing": 21,
                "total": 3,
                "other": 0,
                "terms": [{
                    "term": 8,
                    "count": 3,
                    "label": "8 bits"
                }]
            },
            "chromaSubSampling": {
                "_type": "terms",
                "missing": 24,
                "total": 0,
                "other": 0,
                "terms": []
            },
            "colorSpace": {
                "_type": "terms",
                "missing": 21,
                "total": 3,
                "other": 0,
                "terms": [{
                    "term": "YUV",
                    "count": 3,
                    "label": "YUV"
                }]
            },
            "sampleRate": {
                "_type": "terms",
                "missing": 12,
                "total": 12,
                "other": 0,
                "terms": [{
                    "term": 44100,
                    "count": 6,
                    "label": "44100 Hz"
                }, {
                    "term": 22050,
                    "count": 3,
                    "label": "22050 Hz"
                }, {
                    "term": 8000,
                    "count": 3,
                    "label": "8000 Hz"
                }]
            },
            "bitDepth": {
                "_type": "terms",
                "missing": 21,
                "total": 3,
                "other": 0,
                "terms": [{
                    "term": 8,
                    "count": 3,
                    "label": "8 bits"
                }]
            },
            "size": {
                "_type": "range",
                "ranges": [{
                    "to": 512000,
                    "count": 9,
                    "min": 16418,
                    "max": 333486,
                    "total_count": 9,
                    "total": 1472214,
                    "mean": 163579.33333333
                }, {
                    "from": 512000,
                    "to": 1048576,
                    "count": 3,
                    "min": 647296,
                    "max": 647296,
                    "total_count": 3,
                    "total": 1941888,
                    "mean": 647296
                }, {
                    "from": 1048576,
                    "to": 2097152,
                    "count": 3,
                    "min": 1818411,
                    "max": 1818411,
                    "total_count": 3,
                    "total": 5455233,
                    "mean": 1818411
                }, {
                    "from": 2097152,
                    "to": 5242880,
                    "count": 3,
                    "min": 4118728,
                    "max": 4118728,
                    "total_count": 3,
                    "total": 12356184,
                    "mean": 4118728
                }, {
                    "from": 5242880,
                    "to": 10485760,
                    "count": 0,
                    "total_count": 0,
                    "total": 0,
                    "mean": 0
                }, {
                    "from": 10485760,
                    "count": 3,
                    "min": 14955972,
                    "max": 14955972,
                    "total_count": 3,
                    "total": 44867916,
                    "mean": 14955972
                }]
            },
            "dateIngested": {
                "_type": "range",
                "ranges": [{
                    "to": 1372143600000,
                    "to_str": "1372143600000",
                    "count": 0,
                    "total_count": 0,
                    "total": 0,
                    "mean": 0,
                    "label": "Older than a year"
                }, {
                    "from": 1372143600000,
                    "from_str": "1372143600000",
                    "count": 21,
                    "min": 1403217539000,
                    "max": 1403592094000,
                    "total_count": 21,
                    "total": 29472803268000,
                    "mean": 1403466822285.7,
                    "label": "From last year"
                }, {
                    "from": 1401001200000,
                    "from_str": "1401001200000",
                    "count": 21,
                    "min": 1403217539000,
                    "max": 1403592094000,
                    "total_count": 21,
                    "total": 29472803268000,
                    "mean": 1403466822285.7,
                    "label": "From last month"
                }, {
                    "from": 1403074800000,
                    "from_str": "1403074800000",
                    "count": 21,
                    "min": 1403217539000,
                    "max": 1403592094000,
                    "total_count": 21,
                    "total": 29472803268000,
                    "mean": 1403466822285.7,
                    "label": "From last week"
                }]
            }
        },
        "total": 24
    }

   :query query: search text
   :query sort: field to sort on
   :query sort_direction: sort direction, either ``asc`` (ascending) or ``desc`` (descending)
   :query limit: number of information object works to return
   :query skip: number of information object works to skip (an offset in other words)
   :statuscode 200: no error
