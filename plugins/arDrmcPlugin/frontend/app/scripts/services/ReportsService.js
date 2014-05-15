'use strict';

module.exports = function ($q, $timeout) {

  var resolution = {
    'report1': 'report1Stuff',
    'report2': 'report2Stuff',
    'report3': 'report3Stuff',
    'report4': 'report4Stuff',
    'report5': 'report5Stuff'
  };

  this.asyncReportData = function () {
    var deferred = $q.defer();

    $timeout(function () {
      deferred.resolve(resolution);
    }, 2000);
    return deferred.promise;
  };

};
