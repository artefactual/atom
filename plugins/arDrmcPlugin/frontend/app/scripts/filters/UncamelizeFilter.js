'use strict';

module.exports = function () {

  return function (string) {

    if (!angular.isString(string)) {
      return;
    }

    return string.charAt(0).toUpperCase() + string.substr(1).replace(/[A-Z]/g, ' $&').toLowerCase();

  };

};
