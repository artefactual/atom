Information Object Works
========================

The information objectsi works REST API endpoint allows information object
works data to be browsed.

.. http:get:: /api/informationobjects/works

   Summary of information object works data.

   **Example request**:

   .. sourcecode:: http

      GET /api/informationobjects/works HTTP/1.1
      Host: example.com

   **Example response**:

   .. sourcecode:: http

      HTTP/1.1 200 OK
      Content-Type: application/json

    {
        "results": {
            "422": {
                "title": "EYfRJFWhiIb6zCdWQoyI"
            },
            "426": {
                "title": "Wd2yzFfOKsC5oBWsDU4e"
            },
            "440": {
                "title": "VXDd26vFl9Zi73hjb0Gh"
            },
            "444": {
                "title": "Ij6IDU3JXbaKjqnOKqHr"
            },
            "448": {
                "title": "wlfasaYtaWH8LhTiKO1A"
            },
            "462": {
                "title": "hLabYNOaMHOAEcAvK2f8"
            },
            "466": {
                "title": "WArI0yvajHxuObIBc0Bo"
            },
            "480": {
                "title": "27kq1uayZrlSg7fB0uk5"
            },
            "484": {
                "title": "yUZgbBryjDLyFYJVyt0Q"
            },
            "488": {
                "title": "tCcgTsVYfz8jKibpczDh"
            }
        },
        "facets": {
            "format": {
                "_type": "terms",
                "missing": 52,
                "total": 5,
                "other": 0,
                "terms": [{
                    "term": "Wave",
                    "count": 1,
                    "label": "Wave"
                }, {
                    "term": "QuickTime",
                    "count": 1,
                    "label": "QuickTime"
                }, {
                    "term": "MPEG Video",
                    "count": 1,
                    "label": "MPEG Video"
                }, {
                    "term": "MPEG Audio",
                    "count": 1,
                    "label": "MPEG Audio"
                }, {
                    "term": "AIFF",
                    "count": 1,
                    "label": "AIFF"
                }]
            },
            "videoCodec": {
                "_type": "terms",
                "missing": 52,
                "total": 2,
                "other": 0,
                "terms": [{
                    "term": "jpeg",
                    "count": 1,
                    "label": "jpeg"
                }, {
                    "term": "MPEG-1V",
                    "count": 1,
                    "label": "MPEG-1V"
                }]
            },
            "audioCodec": {
                "_type": "terms",
                "missing": 52,
                "total": 2,
                "other": 0,
                "terms": [{
                    "term": "PCM",
                    "count": 1,
                    "label": "PCM"
                }, {
                    "term": "MPA1L3",
                    "count": 1,
                    "label": "MPA1L3"
                }]
            },
            "resolution": {
                "_type": "terms",
                "missing": 52,
                "total": 1,
                "other": 0,
                "terms": [{
                    "term": 8,
                    "count": 1,
                    "label": "8 bits"
                }]
            },
            "chromaSubSampling": {
                "_type": "terms",
                "missing": 53,
                "total": 0,
                "other": 0,
                "terms": []
            },
            "colorSpace": {
                "_type": "terms",
                "missing": 52,
                "total": 1,
                "other": 0,
                "terms": [{
                    "term": "YUV",
                    "count": 1,
                    "label": "YUV"
                }]
            },
            "sampleRate": {
                "_type": "terms",
                "missing": 52,
                "total": 3,
                "other": 0,
                "terms": [{
                    "term": 44100,
                    "count": 1,
                    "label": "44100 Hz"
                }, {
                    "term": 22050,
                    "count": 1,
                    "label": "22050 Hz"
                }, {
                    "term": 8000,
                    "count": 1,
                    "label": "8000 Hz"
                }]
            },
            "bitDepth": {
                "_type": "terms",
                "missing": 52,
                "total": 1,
                "other": 0,
                "terms": [{
                    "term": 8,
                    "count": 1,
                    "label": "8 bits"
                }]
            },
            "classification": {
                "_type": "terms",
                "missing": 53,
                "total": 0,
                "other": 0,
                "terms": []
            },
            "department": {
                "_type": "terms",
                "missing": 53,
                "total": 0,
                "other": 0,
                "terms": []
            },
            "dateCollected": {
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
            "dateCreated": {
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
                    "count": 1,
                    "min": 1403591774000,
                    "max": 1403591774000,
                    "total_count": 1,
                    "total": 1403591774000,
                    "mean": 1403591774000,
                    "label": "From last year"
                }, {
                    "from": 1400914800000,
                    "from_str": "1400914800000",
                    "count": 1,
                    "min": 1403591774000,
                    "max": 1403591774000,
                    "total_count": 1,
                    "total": 1403591774000,
                    "mean": 1403591774000,
                    "label": "From last month"
                }, {
                    "from": 1402988400000,
                    "from_str": "1402988400000",
                    "count": 1,
                    "min": 1403591774000,
                    "max": 1403591774000,
                    "total_count": 1,
                    "total": 1403591774000,
                    "mean": 1403591774000,
                    "label": "From last week"
                }]
            },
            "totalSize": {
                "_type": "range",
                "ranges": [{
                    "to": 512000,
                    "count": 52,
                    "min": 0,
                    "max": 0,
                    "total_count": 52,
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
                    "count": 1,
                    "min": 44066614,
                    "max": 44066614,
                    "total_count": 1,
                    "total": 44066614,
                    "mean": 44066614
                }]
            }
        },
        "total": 53
    }

   :query query: search text
   :query totalSizeFrom: total size from
   :query totalSizeTo: total size to
   :query sort: field to sort on
   :query sort_direction: sort direction, either ``asc`` (ascending) or ``desc`` (descending)
   :query limit: number of information object works to return
   :query skip: number of information object works to skip (an offset in other words)
   :statuscode 200: no error
