Browse AIPs
===========

The AIPs browse REST API endpoint provides data about AIPs that have been
added to the system.

.. http:get:: /api/aips

   Summary of AIP data.

   **Example request**:

   .. sourcecode:: http

      GET /api/aips HTTP/1.1
      Host: example.com

   **Example response**:

   .. sourcecode:: http

      HTTP/1.1 200 OK
      Content-Type: text/javascript

      {
          "results": [{
              "id": "417",
              "name": "Electro",
              "uuid": "76e6b3ed-1ba6-403b-92cd-5d3169ca86d2",
              "size": "9078374",
              "created_at": "2014-06-19T15:37:31Z",
              "type": {
                  "id": "179",
                  "name": "Artwork component"
              },
              "part_of": {
                  "id": "416",
                  "title": "H49evftydQHN0WidLP7t",
                  "level_of_description_id": "375"
              },
              "digital_object_count": "1"
          }, {
              "id": "457",
              "name": "Robert Hunter",
              "uuid": "56a90e59-cab8-496d-8573-f4a3d0588def",
              "size": "646162",
              "created_at": "2014-06-19T15:37:32Z",
              "type": {
                  "id": "179",
                  "name": "Artwork component"
              },
              "part_of": {
                  "id": "456",
                  "title": "SWEMJ9EnvX8rx1X5SWA7",
                  "level_of_description_id": "375"
              },
              "digital_object_count": "1"
          }],
          "facets": {
              "format": {
                  "_type": "terms",
                  "missing": 63,
                  "total": 0,
                  "other": 0,
                  "terms": []
              },
              "videoCodec": {
                  "_type": "terms",
                  "missing": 63,
                  "total": 0,
                  "other": 0,
                  "terms": []
              },
              "audioCodec": {
                  "_type": "terms",
                  "missing": 63,
                  "total": 0,
                  "other": 0,
                  "terms": []
              },
              "resolution": {
                  "_type": "terms",
                  "missing": 63,
                  "total": 0,
                  "other": 0,
                  "terms": []
              },
              "chromaSubSampling": {
                  "_type": "terms",
                  "missing": 63,
                  "total": 0,
                  "other": 0,
                  "terms": []
              },
              "colorSpace": {
                  "_type": "terms",
                  "missing": 63,
                  "total": 0,
                  "other": 0,
                  "terms": []
              },
              "sampleRate": {
                  "_type": "terms",
                  "missing": 63,
                  "total": 0,
                  "other": 0,
                  "terms": []
              },
              "bitDepth": {
                  "_type": "terms",
                  "missing": 63,
                  "total": 0,
                  "other": 0,
                  "terms": []
              },
              "type": {
                  "_type": "terms",
                  "missing": 15,
                  "total": 48,
                  "other": 0,
                  "terms": [{
                      "term": 181,
                      "count": 15,
                      "label": "Supporting documentation"
                  }, {
                      "term": 180,
                      "count": 14,
                      "label": "Artwork material"
                  }, {
                      "term": 179,
                      "count": 10,
                      "label": "Artwork component"
                  }, {
                      "term": 182,
                      "count": 9,
                      "label": "Supporting technology"
                  }]
              },
              "size": {
                  "_type": "range",
                  "ranges": [{
                      "to": 512000,
                      "count": 0,
                      "total_count": 0,
                      "total": 0,
                      "mean": 0
                  }, {
                      "from": 512000,
                      "to": 1048576,
                      "count": 1,
                      "min": 646162,
                      "max": 646162,
                      "total_count": 1,
                      "total": 646162,
                      "mean": 646162
                  }, {
                      "from": 1048576,
                      "to": 2097152,
                      "count": 6,
                      "min": 1216364,
                      "max": 1654110,
                      "total_count": 6,
                      "total": 8303738,
                      "mean": 1383956.3333333
                  }, {
                      "from": 2097152,
                      "to": 5242880,
                      "count": 18,
                      "min": 2408608,
                      "max": 5215797,
                      "total_count": 18,
                      "total": 63392940,
                      "mean": 3521830
                  }, {
                      "from": 5242880,
                      "to": 10485760,
                      "count": 23,
                      "min": 5676031,
                      "max": 9666391,
                      "total_count": 23,
                      "total": 182412505,
                      "mean": 7930978.4782609
                  }, {
                      "from": 10485760,
                      "count": 0,
                      "total_count": 0,
                      "total": 0,
                      "mean": 0
                  }]
              },
              "dateIngested": {
                  "_type": "range",
                  "ranges": [{
                      "to": 1371970800000,
                      "to_str": "1371970800000",
                      "count": 0,
                      "total_count": 0,
                      "total": 0,
                      "mean": 0,
                      "label": "Older than a year"
                  }, {
                      "from": 1371970800000,
                      "from_str": "1371970800000",
                      "count": 48,
                      "min": 1403192251000,
                      "max": 1403192254000,
                      "total_count": 48,
                      "total": 67353228102000,
                      "mean": 1403192252125,
                  "label": "From last year"
              }, {
                  "from": 1400828400000,
                  "from_str": "1400828400000",
                  "count": 48,
                  "min": 1403192251000,
                  "max": 1403192254000,
                  "total_count": 48,
                  "total": 67353228102000,
                  "mean": 1403192252125,
                  "label": "From last month"
              }, {
                  "from": 1402902000000,
                  "from_str": "1402902000000",
                  "count": 48,
                  "min": 1403192251000,
                  "max": 1403192254000,
                  "total_count": 48,
                  "total": 67353228102000,
                  "mean": 1403192252125,
                  "label": "From last week"
              }]
          }
        },
        "total": 63,
        "overview": {
            "181": {
                "size": 73896282,
                "count": 15
            },
            "180": {
                "size": 77187026,
                "count": 14
            },
            "179": {
                "size": 52874675,
                "count": 10
            },
            "182": {
                "size": 50797362,
                "count": 9
            },
            "unclassified": {
                "count": 15
            },
            "total": {
                "size": 254755345,
                "count": 48
            }
        }
    }

   :query sort: field to sort on
   :query sort_direction: sort direction, either ``asc`` (ascending) or ``desc`` (descending)
   :query limit: number of AIPs to return
   :query skip: number of AIPs to skip (an offset in other words)
   :statuscode 200: no error
