'use strict';

angular.module('momaApp')
  .controller('MainCtrl', function ($scope, $http) {

    $scope.collection = [
      { id: 0, title: "One", children:
        [
          { id: 1, title: "Two" },
          { id: 2, title: "Three" },
        ]
      },
      { id: 3, title: "Four" },
      { id: 4, title: "Five" },
      { id: 5, title: "Six", children:
        [
          { id: 6, title: "Sevein" },
          { id: 7, title: "Eight" },
        ]},
    ];

  });
