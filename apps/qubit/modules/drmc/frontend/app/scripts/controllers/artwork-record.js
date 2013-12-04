'use strict';

angular.module('momaApp')
  .controller('ArtworkRecordCtrl', function ($scope, $modal, atomGlobals, $sce) {

    $scope.atomGlobals = atomGlobals;

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
          { id: 39, title: '.mov Uncompressed 10bit PAL', level: 'digital-object' }
        ]}
      ]}
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

    //hack - must fix
    var videoUrl1 = $scope.atomGlobals.relativeUrlRoot + "/apps/qubit/modules/drmc/frontend/assets/play-dead-channel-1/1098_2005_a_trim.mp4";
    $scope.videoUrl1 = $sce.trustAsResourceUrl(videoUrl1);

    var videoUrl2 = $scope.atomGlobals.relativeUrlRoot + "/apps/qubit/modules/drmc/frontend/assets/play-dead-channel-2/1098_2005_b_trim.mp4";
    $scope.videoUrl2 = $sce.trustAsResourceUrl(videoUrl2);

    var videoUrl3 = $scope.atomGlobals.relativeUrlRoot + "/apps/qubit/modules/drmc/frontend/assets/play-dead-channel-3/1098_2005_c_trim.mp4";
    $scope.videoUrl3 = $sce.trustAsResourceUrl(videoUrl3);


});
