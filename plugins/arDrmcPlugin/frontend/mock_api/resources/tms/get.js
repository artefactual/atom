var urlParts = url.split('/'),
    mainRoutes = {},
    criteria = {};

// remove empty first element
urlParts.shift();

// needed by GetTombstoneData
if (query.ObjectNumber !== 'undefined') {
  criteria.ObjectNumber = query.ObjectNumber;
}

// needed by GetComponentDetails
if (query.Component !== 'undefined') {
  criteria.Component = query.Component;
}

// HTTP GET /tms/GetTombstoneData
mainRoutes.GetTombstoneData = function() {
  var results = [];

  criteria.limit = 1;
  // fetch TMS objects matching criteria
  dpd.tmsraw.get(criteria, function(tmsObjects) {
    tmsObjects.forEach(function(tmsObject) {
      results.push(tmsObject);
    });
    setResult(results[0]);
  });
};

mainRoutes.GetComponentDetails = function() {
  var results = [];

  criteria.limit = 1;
  // fetch TMS objects matching criteria
  dpd.tmscomponentraw.get(criteria, function(tmsComponents) {
    tmsComponents.forEach(function(tmsComponent) {
      results.push(tmsComponent);
    });
    setResult({
      'GetComponentDetailsResult': results[0]
    });
  });
};

mainRoutes.GetTombstoneDataRest = function() {};
mainRoutes.GetTombstoneDateId = function() {};
mainRoutes.GetObjectID = function() {};
mainRoutes.GetObjectPackageID = function() {};
mainRoutes.GetObjectPackage = function() {};
mainRoutes.GetObjectPackageTitle = function() {};
mainRoutes.GetExhibitionObjects = function() {};

if (typeof mainRoutes[urlParts[0]] != 'undefined') {
  resultData = mainRoutes[urlParts[0]]();
} else {
  resultData = {'message': 'Bad URL.'};
}
