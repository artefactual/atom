'use strict';

module.exports = function ($scope, SETTINGS) {
  $scope.headerPartialPath = SETTINGS.viewsPath + '/layout/header.html';
  $scope.footerPartialPath = SETTINGS.viewsPath + '/layout/footer.html';
};
