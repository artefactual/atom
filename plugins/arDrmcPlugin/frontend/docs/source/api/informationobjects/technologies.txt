Information Object Technologies
===============================

The information object technologies REST API endpoint allows information object
technology data to be browsed.

.. http:get:: /api/informationobjects/technologies

   Summary of information object technology data.

   **Example request**:

   .. sourcecode:: http

      GET /api/informationobjects/technologies HTTP/1.1
      Host: example.com

   **Example response**:

   .. sourcecode:: http

      HTTP/1.1 200 OK
      Content-Type: application/json

    {
        "results": {
            "512": {
                "title": "Open source",
                "inherited_title": "Codecs \u00bb Video codecs \u00bb Open source",
                "collection_root_id": 510,
                "id": 512
            },
            "516": {
                "title": "Propietary",
                "inherited_title": "Codecs \u00bb Video codecs \u00bb Propietary",
                "collection_root_id": 510,
                "id": 516
            },
            "521": {
                "title": "Open source",
                "inherited_title": "Codecs \u00bb Audio codecs \u00bb Open source",
                "collection_root_id": 510,
                "id": 521
            },
            "525": {
                "title": "WMA",
                "inherited_title": "Codecs \u00bb Audio codecs \u00bb Propietary \u00bb WMA",
                "collection_root_id": 510,
                "id": 525
            },
            "513": {
                "title": "x264",
                "inherited_title": "Codecs \u00bb Video codecs \u00bb Open source \u00bb x264",
                "collection_root_id": 510,
                "id": 513
            },
            "517": {
                "title": "WMV 7",
                "inherited_title": "Codecs \u00bb Video codecs \u00bb Propietary \u00bb WMV 7",
                "collection_root_id": 510,
                "id": 517
            },
            "522": {
                "title": "FLAC",
                "inherited_title": "Codecs \u00bb Audio codecs \u00bb Open source \u00bb FLAC",
                "collection_root_id": 510,
                "id": 522
            },
            "526": {
                "title": "MP3",
                "inherited_title": "Codecs \u00bb Audio codecs \u00bb Propietary \u00bb MP3",
                "collection_root_id": 510,
                "id": 526
            },
            "511": {
                "title": "Video codecs",
                "inherited_title": "Codecs \u00bb Video codecs",
                "collection_root_id": 510,
                "id": 511
            },
            "515": {
                "title": "Xvid",
                "inherited_title": "Codecs \u00bb Video codecs \u00bb Open source \u00bb Xvid",
                "collection_root_id": 510,
                "id": 515
            }
        },
        "facets": {
            "format": {
                "_type": "terms",
                "missing": 17,
                "total": 0,
                "other": 0,
                "terms": []
            },
            "videoCodec": {
                "_type": "terms",
                "missing": 17,
                "total": 0,
                "other": 0,
                "terms": []
            },
            "audioCodec": {
                "_type": "terms",
                "missing": 17,
                "total": 0,
                "other": 0,
                "terms": []
            },
            "resolution": {
                "_type": "terms",
                "missing": 17,
                "total": 0,
                "other": 0,
                "terms": []
            },
            "chromaSubSampling": {
                "_type": "terms",
                "missing": 17,
                "total": 0,
                "other": 0,
                "terms": []
            },
            "colorSpace": {
                "_type": "terms",
                "missing": 17,
                "total": 0,
                "other": 0,
                "terms": []
            },
            "sampleRate": {
                "_type": "terms",
                "missing": 17,
                "total": 0,
                "other": 0,
                "terms": []
            },
            "bitDepth": {
                "_type": "terms",
                "missing": 17,
                "total": 0,
                "other": 0,
                "terms": []
            },
            "dateIngested": {
                "_type": "range",
                "ranges": [{
                    "to": 1372057200000,
                    "to_str": "1372057200000",
                    "count": 0,
                    "total_count": 0,
                    "total": 0,
                    "mean": 0,
                    "label": "Older than a year"
                }, {
                    "from": 1372057200000,
                    "from_str": "1372057200000",
                    "count": 0,
                    "total_count": 0,
                    "total": 0,
                    "mean": 0,
                    "label": "From last year"
                }, {
                    "from": 1400914800000,
                    "from_str": "1400914800000",
                    "count": 0,
                    "total_count": 0,
                    "total": 0,
                    "mean": 0,
                    "label": "From last month"
                }, {
                    "from": 1402988400000,
                    "from_str": "1402988400000",
                    "count": 0,
                    "total_count": 0,
                    "total": 0,
                    "mean": 0,
                    "label": "From last week"
                }]
            },
            "totalSize": {
                "_type": "range",
                "ranges": [{
                    "to": 512000,
                    "count": 17,
                    "min": 0,
                    "max": 0,
                    "total_count": 17,
                    "total": 0,
                    "mean": 0
                }, {
                    "from": 512000,
                    "to": 1048576,
                    "count": 0,
                    "total_count": 0,
                    "total": 0,
                    "mean": 0
                }, {
                    "from": 1048576,
                    "to": 2097152,
                    "count": 0,
                    "total_count": 0,
                    "total": 0,
                    "mean": 0
                }, {
                    "from": 2097152,
                    "to": 5242880,
                    "count": 0,
                    "total_count": 0,
                    "total": 0,
                    "mean": 0
                }, {
                    "from": 5242880,
                    "to": 10485760,
                    "count": 0,
                    "total_count": 0,
                    "total": 0,
                    "mean": 0
                }, {
                    "from": 10485760,
                    "count": 0,
                    "total_count": 0,
                    "total": 0,
                    "mean": 0
                }]
            }
        },
        "total": 17
    }

   :query query: search text
   :query onlyRoot: only root items
   :query totalSizeFrom: total size from
   :query totalSizeTo: total size to
   :query sort: field to sort on
   :query sort_direction: sort direction, either ``asc`` (ascending) or ``desc`` (descending)
   :query limit: number of information object components to return
   :query skip: number of information object components to skip (an offset in other words)
   :statuscode 200: no error
