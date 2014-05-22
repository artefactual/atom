'use strict';

module.exports = function () {

  return function (input) {
    if (!input) {
      return;
    }

    var date = new Date(input);
    if (angular.isUndefined(date)) {
      return;
    }

    return date.toLocaleDateString() + ' @ ' + date.toLocaleTimeString();
  };

};
