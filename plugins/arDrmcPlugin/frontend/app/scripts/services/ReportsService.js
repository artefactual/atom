'use strict';

module.exports = function ($q) {

  var deferred = $q.defer();

  this.asyncReport = function (response) {
    setTimeout(function () {
      deferred.resolve();
    }, 2000);
    return deferred.promise;
  };

  var reportData = {
    'report1': 'report1Stuff',
    'report2': 'report1Stuff',
    'report3': 'report1Stuff',
    'report4': 'report1Stuff',
    'report5': 'report1Stuff'
  };
};
