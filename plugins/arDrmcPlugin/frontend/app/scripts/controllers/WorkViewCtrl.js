'use strict';

module.exports = function ($scope, $stateParams) {

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

};
