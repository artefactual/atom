DRMC-MA: Digital Repository for Museum Collections
==================================================

The DRMC frontend is built with AngularJS. Interaction with AtoM is through a
REST API. For testing/development purposes, a mock REST API server, built in
Deployd, allows the front end to be tested/evaluated without accessing a
production API.


Branches
--------

* master branch

  This branch triggers a job in Jenkins that deploys the testing site
  (see http://moma.test.artefactual.com in a per-commit basis.
  It's basically an extra step after qa/devel that we are adding in order to ensure
  that we are not breaking the testing site with new code.
  This is all we should be doing in master really:

    $ git checkout master
    $ git pull --ff-only git@git.artefactual.com:client/moma/atom.git qa/devel
    $ git push # ... if everything went well! Remember, this is triggering a deployment.

* qa/devel

  We merge topic branches here.
  If it works, master will be pulling from here.

* dev/*

  Development/topic branches. Here you can break things.


Installation of frontend
------------------------

Install system dependencies (tested in Ubuntu 12.04)::

  $ sudo add-apt-repository ppa:chris-lea/node.js
  $ sudo apt-get update
  $ sudo apt-get install nodejs build-essential # Yep, you're going to need make, gcc, etc...
  $ sudo npm install -g grunt-cli

Install JavaScript dependencies (from /plugins/arDrmcPlugin/frontend)::

  $ npm install
  $ grunt build

Clear the symfony cache (from the AtoM root directory)::

  $ php symfony cc

You can run "grunt watch" to detect changes during development and trigger
the build. Take into account that "grunt build" will be still necessary to
be executed once when you are configuring a new environment or the vendor
browserify build has changed. See Gruntfile.js for more details.


Installation of mock API server
-------------------------------

The mock REST API server usually runs on port 2403, although if this port is
in use Deployd will try the next available port (2404 for example) and run on
that. When the mock REST API server is running a dashboard web application,
which can be used for adding/editing mock data, is available at /dashboard.

Install mock REST API server system and JavaScript dependencies::

  $ sudo apt-get install mongodb
  $ sudo npm install -g deployd
  $ cd mock_api
  $ npm install

Run the mock REST API server (from /plugins/arDrmcPlugin/frontend)::

  $ ./start_mock_api

POST sample data to the mock REST API server (from /plugins/arDrmcPlugin/frontend)::

  $ ./mock_api/populate_sample_data <TCP port of API server>

Update the AIP sample data with modifications that can be committed to Git
(from /plugins/arDrmcPlugin/frontend)::

  $ curl http://127.0.0.1:2403/aips-raw | python -mjson.tool > mock_api/sample_data/aips.json

Generate sample data for 200 random AIPs (from /plugins/arDrmcPlugin/frontend)::

  $ ./mock_api/generate_sample_aips 200 | python -mjson.tool > mock_api/sample_data/aips.json

Configuring nginx to proxy the mock REST API server
```````````````````````````````````````````````````

You can use nginx as a proxy so the regular API URLs will pass data to and
from the mock REST API server.

Alter /etc/nginx/sites-available/default to include::

  location /api/ {
    rewrite  ^/api/(.*) /$1 break;
    proxy_pass http://localhost:2405/;
  }

  location /index.php/api/ {
    rewrite ^/index.php/api/(.*) /$1 break;
    proxy_pass http://localhost:2403/;
  }

Next, change the FastCGI location criteria to::

  location ~ ^/(index|qubit_dev)\.php(/!(api)|$) {

Restart nginx::

  $ sudo service nginx restart
