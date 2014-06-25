Browse/Create Information Objects
=================================

The information objects browse/create REST API endpoint provides data about
information objects that exist in the system and allows new ones to be
created.

.. http:get:: /api/informationobjects

   Summary of information object data.

   **Example request**:

   .. sourcecode:: http

      GET /api/informationobjects HTTP/1.1
      Host: example.com

   **Example response**:

   .. sourcecode:: http

      HTTP/1.1 200 OK
      Content-Type: application/json

    {
        "results": {
            "422": {
                "updatedAt": "2014-06-19T15:37:31Z",
                "parentId": "1",
                "hasDigitalObject": false,
                "sourceCulture": "en",
                "createdAt": "2014-06-19T15:37:31Z",
                "ancestors": [
                    "1"
                ],
                "i18n": {
                    "languages": [
                        "en"
                    ],
                    "en": {
                        "title": "EYfRJFWhiIb6zCdWQoyI"
                    }
                },
                "publicationStatusId": "159",
                "levelOfDescriptionId": "375",
                "slug": "eyfrjfwhiib6zcdwqoyi",
                "id": 422,
                "title": "EYfRJFWhiIb6zCdWQoyI"
            },
            "426": {
                "updatedAt": "2014-06-19T15:37:31Z",
                "parentId": "1",
                "hasDigitalObject": false,
                "sourceCulture": "en",
                "createdAt": "2014-06-19T15:37:31Z",
                "ancestors": [
                    "1"
                ],
                "i18n": {
                    "languages": [
                        "en"
                    ],
                    "en": {
                        "title": "Wd2yzFfOKsC5oBWsDU4e"
                    }
                },
                "publicationStatusId": "159",
                "levelOfDescriptionId": "375",
                "slug": "wd2yzffoksc5obwsdu4e",
                "id": 426,
                "title": "Wd2yzFfOKsC5oBWsDU4e"
            }
        },
        "facets": [ ],
        "total": 101
    }

   :query sort: field to sort on
   :query sort_direction: sort direction, either ``asc`` (ascending) or ``desc`` (descending)
   :query limit: number of information objects to return
   :query skip: number of information objects to skip (an offset in other words)
   :statuscode 200: no error

.. http:post:: /api/informationobjects

   Create an information object.

   **Example request**:

   .. sourcecode:: http

      POST /api/informationobjects HTTP/1.1
      Host: example.com

    {
        "title": "Book of Cats",
        "description": "This is a book about cats."
    }

   **Example response**:

   .. sourcecode:: http

      HTTP/1.1 200 OK
      Content-Type: application/json

    {
        "id": 652,
        "parent_id":1
    }

   :statuscode 200: no error
