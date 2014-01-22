'use strict';

angular.module('momaApp.controllers')
  .controller('WorksViewCtrl', function ($scope, $stateParams) {

    $scope.work = {
      id: $stateParams.id,
      title: 'Play Dead; Real Time',
      accessionNo: '1098.2005.a-c',
      objectId: '100620',
      date: '2003',
      artist: 'Douglas Gordon',
      classification: 'Installation',
      medium: 'Three-channel video',
      dimensions: '19:11 min, 14:44 min. (on larger screens), 21:58 min. (on monitor). Minimum Room Size: 24.8m x 13.07m',
      description: 'Exhibition materials: 3 DVD and players, 2 projectors, 3 monitor, 2 screens. The complete work is a three-screen piece, consisting of one retro projection, one front projection and one monitor. See file for installation instructions. One monitor and two projections on screens 19.69 X 11.38 feet. Viewer must be able to walk around screens.'
    };

    $scope.collectionTest1 = [
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
          { id: 42, title: '.mov H264', level: 'digital-object' }
        ]},
        { id: 11, title: 'Installation Documentation', level: 'description' }
      ]}
    ];

    $scope.collectionTest2 = [
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

  });
