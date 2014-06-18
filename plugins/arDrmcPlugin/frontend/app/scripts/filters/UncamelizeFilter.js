'use strict';

module.exports = function () {

  return function (string) {

    if (!String(string)) {
      return;
    }

    return string.charAt(0).toUpperCase() + string.substr(1).replace(/[A-Z]/g, ' $&').toLowerCase();

  };

};
