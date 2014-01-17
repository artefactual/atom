'use strict';

angular.module('momaApp')
  .controller('RestTestsCtrl', function ($scope, $http, atomGlobals) {

    var requestUrl = atomGlobals.relativeUrlFrontend + '/api';

    function restTest()
    {
      return $http({
        method: 'GET',
        url: atomGlobals.relativeUrlFrontend + '/api' + resource
      }).success(function(data, status, headers, config) {

      }).error(function(data, status, headers, config) {

      });
    }

    restTest($http, 'GET', requestUrl + '/dashboard');

});
