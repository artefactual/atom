(function () {

  'use strict';

  module.exports.values = function (object) {
    return Object.keys(object).map(function (key) {
      return object[key];
    });
  };

})();
