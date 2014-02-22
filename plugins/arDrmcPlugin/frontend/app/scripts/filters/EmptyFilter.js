'use strict';

// This allows for addition of dom
//elements around data, and returns
// empty if field contains no data

module.exports = function () {

  return function (x) {
    if (!(x === undefined || x === null)) {
      return '(' + x + ')';
    } else {
      return;
    }
  };
};
