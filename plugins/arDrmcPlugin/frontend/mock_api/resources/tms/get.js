var urlParts = url.split('/'),
    mainRoutes = {},
    resultData;

// remove empty first element
urlParts.shift();

mainRoutes.GetTombstoneData = function() {
  var resultData = {};
  resultData.message = 'Called GetTombstoneData.';
  return resultData;
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

setResult(resultData);
