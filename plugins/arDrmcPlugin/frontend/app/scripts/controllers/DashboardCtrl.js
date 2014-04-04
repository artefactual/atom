'use strict';

module.exports = function ($scope) {
  $scope.tabs = [
    {
      title: 'Recent activity',
      template: 'tmpl-recent-activity'
    },
    {
      title: 'Ingestion',
      template: 'tmpl-ingestion'
    },
  ];
};
