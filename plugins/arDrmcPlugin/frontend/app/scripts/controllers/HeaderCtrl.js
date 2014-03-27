'use strict';

module.exports = function ($scope, $modal, SETTINGS) {

  $scope.openSupportingTechnologyModal = function () {
    var modalInstance = $modal.open({
      templateUrl: SETTINGS.viewsPath + '/modals/add-supporting-technology.html',
      backdrop: true,
      controller: 'AddSupportingTechnologyCtrl',
      scope: $scope
    });
    modalInstance.result.then(function (result) {
      console.log('tech modal result', result);
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
