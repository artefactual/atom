'use strict';

module.exports = function ($scope, SETTINGS, ModalEditDcMetadataService) {

  $scope.openEditDcModal = function () {
    ModalEditDcMetadataService.create().result.then(function () {
      console.log('Muuuu');
    });
  };

  // Current user. This is just a mockup for the view. Auth not implemented yet.
  $scope.user = {
    username: 'benfinoradin',
    name: 'Ben',
    email: 'ben_fino-radin@moma.org',
    gravatar: 'http://www.gravatar.com/avatar/ab6aae7aa48f96cf5ea5ab5d3aa2247d?s=25'
  };

};
