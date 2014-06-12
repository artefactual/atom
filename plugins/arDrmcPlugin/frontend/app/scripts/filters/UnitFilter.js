'use strict';

// You can specify how precise the filter is, defaults to 1 decimal

module.exports = function () {
  return function (size, precision) {
    // Set default precision to 1
    if (typeof precision === 'undefined' || precision === 0 || precision === null) {
      precision = 1;
    }

    if (typeof size === 'undefined' || size === null || isNaN(size)) {
      return;
    }

    if (size <= 0) {
      return 0;
    }

    var sizes = ['bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB'];
    var unit = 0;

    if (size < 1024) {
      return Number(size) + ' ' + sizes[unit];
    }

    // This loop divides size by 1024 until the number is < 1024, moving to
    // new [sizes] unit on each loop
    while (size >= 1024) {
      unit++;
      size = size / 1024;
    }

    var power = Math.pow(10, precision);
    var fullValue = Math.ceil (size * power);

    size = fullValue / power;

    return size + ' ' + sizes[unit];
  };
};
