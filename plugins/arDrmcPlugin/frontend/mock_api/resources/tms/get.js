var urlParts = url.split('/'),
    mainRoutes = {},
    criteria = {};

// remove empty first element
urlParts.shift();

// HTTP GET /tms/GetTombstoneData
mainRoutes.GetTombstoneData = function() {
  var results = [];

  // fetch TMS objects matching criteria
  dpd.tmsraw.get(criteria, function(tmsObjects) {
    tmsObjects.forEach(function(tmsObject) {
      results.push(tmsObject);
    });
    setResult(results);
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
