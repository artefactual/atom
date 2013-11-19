'use strict';

angular.module('momaApp')
  .controller('ArtworkRecordCtrl', function ($scope, $http, atomGlobals) {

    $scope.atomGlobals = atomGlobals;

    $scope.collection = [
      { id: 0, title: 'Play Dead; Real Time', level: 'Work', children: [
            { id: 20, title: 'Components', level: 'Expression', children:
              [
                { id: 31, title: 'DVD', level: 'Component' },
                { id: 32, title: 'DVD', level: 'Component' },
                { id: 33, title: 'DVD', level: 'Component' },
                { id: 34, title: 'Digital Betacam', level: 'Component' },
                { id: 35, title: 'Digital Betacam', level: 'Component' },
                { id: 36, title: 'Digital Betacam', level: 'Component' },
                { id: 37, title: '.mov Uncompressed 10bit PAL', level: 'Component' },
                { id: 38, title: '.mov Uncompressed 10bit PAL', level: 'Component' },
                { id: 39, title: '.mov Uncompressed 10bit PAL', level: 'Component' },
                { id: 40, title: '.mov H264', level: 'Component' },
                { id: 41, title: '.mov H264', level: 'Component' },
                { id: 42, title: '.mov H264', level: 'Component' }
              ]
        },
        { id: 11, title: 'Installation Documentation', level: 'Expression' }
      ]}
    ];

    $scope.relations = [
      { source: 35, target: 31, type: 'is derivative of' }
    ];

});
