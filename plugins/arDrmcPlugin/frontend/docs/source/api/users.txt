User Authentication REST API
============================

The users REST API endpoint allows a user to authenticate and returns data
about the user.

.. http:get:: /api/users

   User information.

   **Example request**:

   .. sourcecode:: http

      GET /api/users HTTP/1.1
      Host: example.com
      Accept: application/json, text/javascript

   **Example response**:

   .. sourcecode:: http

      HTTP/1.1 200 OK
      Content-Type: text/javascript

    {
    }

   :statuscode 200: no error
