(function () {

  'use strict';

  angular.module('drmc.controllers').controller('BodyCtrl', function ($scope, SETTINGS) {

    $scope.headerPartialPath = SETTINGS.viewsPath + '/layout/header.html';
    $scope.footerPartialPath = SETTINGS.viewsPath + '/layout/footer.html';

  });

})();
