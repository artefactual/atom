Actor REST API
==============

The actors REST API endpoint provides data about actors that exist in the
system.

.. http:get:: /api/actors

   Summary of actor data.

   **Example request**:

   .. sourcecode:: http

      GET /api/actors HTTP/1.1
      Host: example.com
      Accept: application/json, text/javascript

   **Example response**:

   .. sourcecode:: http

      HTTP/1.1 200 OK
      Content-Type: text/javascript

    {
        "results": {
            "621": {
                "updatedAt": "2014-06-23T14:37:59Z",
                "i18n": {
                    "languages": ["en"],
                    "en": {
                        "authorizedFormOfName": "Rick LaRue"
                    }
                },
                "slug": "rick-larue",
                "sourceCulture": "en",
                "createdAt": "2014-06-23T14:37:59Z",
                "authorized_form_of_name": "Rick LaRue"
            }
        },
        "facets": [],
        "total": 1
    }

   :query sort: field to sort on
   :query sort_direction: sort direction, either ``asc`` (ascending) or ``desc`` (descending)
   :query limit: number of actors to return
   :query skip: number of actors to skip (an offset in other words)
   :statuscode 200: no error
