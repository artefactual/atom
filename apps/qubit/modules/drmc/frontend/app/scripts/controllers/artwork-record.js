'use strict';

angular.module('momaApp')
  .controller('ArtworkRecordCtrl', function ($scope, $modal, atomGlobals) {

    $scope.atomGlobals = atomGlobals;

    $scope.collection = [
      { id: 0, title: 'Play Dead; Real Time', level: 'Work', children: [
        { id: 20, title: 'Components', level: 'Expression', children: [
          { id: 31, title: 'DVD', level: 'PhysicalComponent' },
          { id: 32, title: 'DVD', level: 'PhysicalComponent' },
          { id: 33, title: 'DVD', level: 'PhysicalComponent' },
          { id: 34, title: 'Digital Betacam', level: 'PhysicalComponent' },
          { id: 35, title: 'Digital Betacam', level: 'PhysicalComponent' },
          { id: 36, title: 'Digital Betacam', level: 'PhysicalComponent' },
          { id: 37, title: '.mov Uncompressed 10bit PAL', level: 'DigitalObject' },
          { id: 38, title: '.mov Uncompressed 10bit PAL', level: 'DigitalObject' },
          { id: 39, title: '.mov Uncompressed 10bit PAL', level: 'DigitalObject' },
          { id: 40, title: '.mov H264', level: 'DigitalObject' },
          { id: 41, title: '.mov H264', level: 'DigitalObject' },
          { id: 42, title: '.mov H264', level: 'DigitalObject' } ]},
        { id: 11, title: 'Installation Documentation', level: 'Expression' }]}
    ];


});
