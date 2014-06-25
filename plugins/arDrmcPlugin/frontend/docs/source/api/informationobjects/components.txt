Information Object Components
=============================

The information object components REST API endpoint allows information object
component data to be browsed.

.. http:get:: /api/informationobjects/components

   Summary of information object components data.

   **Example request**:

   .. sourcecode:: http

      GET /api/informationobjects/components HTTP/1.1
      Host: example.com

   **Example response**:

   .. sourcecode:: http

      HTTP/1.1 200 OK
      Content-Type: application/json

    {
        "results": {
            "543": {
                "name": "1098.2005.a.AV",
                "lod_name": "Exhibition format",
                "artwork_id": "527",
                "artwork_title": "Play Dead; Real Time"
            },
            "552": {
                "name": "1098.2005.b.x1",
                "lod_name": "Artist supplied master",
                "artwork_id": "527",
                "artwork_title": "Play Dead; Real Time"
            },
            "556": {
                "name": "1098.2005.c.x2",
                "lod_name": "Artist verified proof",
                "artwork_id": "527",
                "artwork_title": "Play Dead; Real Time"
            },
            "561": {
                "name": "1098.2005.c.x4",
                "lod_name": "Archival master",
                "artwork_id": "527",
                "artwork_title": "Play Dead; Real Time"
            },
            "548": {
                "name": "1098.2005.a.x1",
                "lod_name": "Artist supplied master",
                "artwork_id": "527",
                "artwork_title": "Play Dead; Real Time"
            },
            "553": {
                "name": "1098.2005.b.x2",
                "lod_name": "Artist verified proof",
                "artwork_id": "527",
                "artwork_title": "Play Dead; Real Time"
            },
            "557": {
                "name": "1098.2005.c.x3",
                "lod_name": "Artist verified proof",
                "artwork_id": "527",
                "artwork_title": "Play Dead; Real Time"
            },
            "546": {
                "name": "1098.2005.c.AV",
                "lod_name": "Exhibition format",
                "artwork_id": "527",
                "artwork_title": "Play Dead; Real Time"
            },
            "555": {
                "name": "1098.2005.c.x1",
                "lod_name": "Artist supplied master",
                "artwork_id": "527",
                "artwork_title": "Play Dead; Real Time"
            },
            "559": {
                "name": "1098.2005.a.x4",
                "lod_name": "Archival master",
                "artwork_id": "527",
                "artwork_title": "Play Dead; Real Time"
            }
        },
        "facets": {
            "format": {
                "_type": "terms",
                "missing": 15,
                "total": 0,
                "other": 0,
                "terms": []
            },
            "videoCodec": {
                "_type": "terms",
                "missing": 15,
                "total": 0,
                "other": 0,
                "terms": []
            },
            "audioCodec": {
                "_type": "terms",
                "missing": 15,
                "total": 0,
                "other": 0,
                "terms": []
            },
            "resolution": {
                "_type": "terms",
                "missing": 15,
                "total": 0,
                "other": 0,
                "terms": []
            },
            "chromaSubSampling": {
                "_type": "terms",
                "missing": 15,
                "total": 0,
                "other": 0,
                "terms": []
            },
            "colorSpace": {
                "_type": "terms",
                "missing": 15,
                "total": 0,
                "other": 0,
                "terms": []
            },
            "sampleRate": {
                "_type": "terms",
                "missing": 15,
                "total": 0,
                "other": 0,
                "terms": []
            },
            "bitDepth": {
                "_type": "terms",
                "missing": 15,
                "total": 0,
                "other": 0,
                "terms": []
            },
            "classification": {
                "_type": "terms",
                "missing": 15,
                "total": 0,
                "other": 0,
                "terms": []
            },
            "type": {
                "_type": "terms",
                "missing": 0,
                "total": 15,
                "other": 0,
                "terms": [{
                    "term": 379,
                    "count": 6,
                    "label": "Artist verified proof"
                }, {
                    "term": 381,
                    "count": 3,
                    "label": "Exhibition format"
                }, {
                    "term": 380,
                    "count": 3,
                    "label": "Archival master"
                }, {
                    "term": 378,
                    "count": 3,
                    "label": "Artist supplied master"
                }]
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
                    "count": 15,
                    "min": 0,
                    "max": 0,
                    "total_count": 15,
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
        "total": 15
    }

   :query query: search text
   :query totalSizeFrom: total size from
   :query totalSizeTo: total size to
   :query sort: field to sort on
   :query sort_direction: sort direction, either ``asc`` (ascending) or ``desc`` (descending)
   :query limit: number of information object components to return
   :query skip: number of information object components to skip (an offset in other words)
   :statuscode 200: no error
