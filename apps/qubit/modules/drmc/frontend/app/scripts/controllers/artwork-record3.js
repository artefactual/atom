'use strict';

angular.module('momaApp')
  .controller('ArtworkRecord3Ctrl', function ($scope, $modal, atomGlobals) {

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

    $scope.techRelationships = [
      { id: '1', name: 'requires' },
      { id: '2', name: 'replaces' },
      { id: '3', name: 'references' },
      { id: '4', name: 'has format' },
      { id: '5', name: 'is format on' },
      { id: '6', name: 'is part of' },
      { id: '7', name: 'is replaced by' },
      { id: '8', name: 'is required by' }
    ];

    $scope.techRelation = '1';
    $scope.pushSelect = function(){
      $scope.techRelationships.push({ id: '' + ($scope.techRelationships.length + 1), name: '' });
      };

});
