Taxonomy REST API
=================

The taxonomy REST API endpoint provides data about terms that exist in the
system.

.. http:get:: /api/taxonomies/<id>

   List of taxonomy terms.

   **Example request**:

   .. sourcecode:: http

      GET /api/taxonomies/31 HTTP/1.1
      Host: example.com
      Accept: application/json, text/javascript

   **Example response**:

   .. sourcecode:: http

      HTTP/1.1 200 OK
      Content-Type: text/javascript

    {
        "terms": [{
            "id": 218,
            "name": "Full"
        }, {
            "id": 220,
            "name": "Minimal"
        }, {
            "id": 219,
            "name": "Partial"
        }]
    }

   :statuscode 200: no error
