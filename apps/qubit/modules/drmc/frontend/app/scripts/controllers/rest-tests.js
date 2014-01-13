'use strict';

angular.module('momaApp')
  .controller('RestTestsCtrl', function ($scope, $http, atomGlobals) {

    var requestUrl = atomGlobals.relativeUrlFrontend + '/api';

    function restTest()
    {
      return $http({
        method: method,
        url: atomGlobals.relativeUrlFrontend + '/api' + resource
      }).success(function(data, status, headers, config) {
        console.log('SUCCESS', resource, status);
      }).error(function(data, status, headers, config) {
        console.log('ERROR', resource, status);
      });
    }

    restTest($http, 'GET', requestUrl + '/dashboard');

});


function restTest($http, method, resource)
{

}
