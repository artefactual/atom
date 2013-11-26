'use strict';

angular.module('momaApp', ['$strap.directives']);
  .controller('TechModalCtrl',  function ($scope, $modal, $http, atomGlobals) {

    $scope.atomGlobals = atomGlobals;

    $scope.modal = {
      "content": "Hello Modal",
      "saved": false
    };
});
