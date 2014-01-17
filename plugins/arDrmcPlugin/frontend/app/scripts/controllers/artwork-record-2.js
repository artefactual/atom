'use strict';

angular.module('momaApp.controllers')
  .controller('ArtworkRecord2Ctrl', function ($scope, $modal, $sce, ATOM_CONFIG) {

    $scope.collection = [
      { id: 0, title: 'Play Dead; Real Time', level: 'work', children: [
        { id: 20, title: 'Components', level: 'description', children: [
          { id: 31, title: 'DVD', level: 'physical-component' },
          { id: 32, title: 'DVD', level: 'physical-component' },
          { id: 33, title: 'DVD', level: 'physical-component' },
          { id: 34, title: 'Digital Betacam', level: 'physical-component' },
          { id: 35, title: 'Digital Betacam', level: 'physical-component' },
          { id: 36, title: 'Digital Betacam', level: 'physical-component' },
          { id: 37, title: '.mov Uncompressed 10bit PAL', level: 'digital-object' },
          { id: 38, title: '.mov Uncompressed 10bit PAL', level: 'digital-object' },
          { id: 39, title: '.mov Uncompressed 10bit PAL', level: 'digital-object' },
          { id: 40, title: '.mov H264', level: 'digital-object' },
          { id: 41, title: '.mov H264', level: 'digital-object' },
          { id: 42, title: '.mov H264', level: 'digital-object' } ]},
        { id: 11, title: 'Installation Documentation', level: 'description' }]}
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

    // The video link seems untrusted by angular. This needs $sce in params too.
    var videoUrl = ATOM_CONFIG.assetsPath + '/play-dead-channel-1/play-dead-channel-1.mp4';
    $scope.videoUrl = $sce.trustAsResourceUrl(videoUrl);

});
