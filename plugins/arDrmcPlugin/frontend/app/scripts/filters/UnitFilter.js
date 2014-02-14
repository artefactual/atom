'use strict';

// This size conversion filter takes an array and
// applies the correct suffix dependant on the
// order of magnitude specified by the number
// it's called upon.

// You can specify how precise the filter is
// by adding : and adding how many numbers
// after the decimal you'd like to calculate

module.exports = function () {

  return function (size, precision) {

    // Set default precision to 1
    if (precision === 0 || precision === null) {
      precision = 1;
    }

    if (size <= 0 || size === null) {
      return;
    }

    if (!isNaN(size)){
      var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB'];
      var unit = 0;

      // Using base 10 system, 2^10 bytes = 1 kb.
      // This returns number in bytes if < 1 kb

      if (size < 1024) {
        return Number(size) + ' ' + sizes[unit];
      }

      // This loop divides size by 1024
      // until the number is < 1024,
      // moving to new [sizes] unit
      // on each loop

      while (size >= 1024) {
        unit++;
        size = size / 1024;
      }

      // 'precision' here is how many times
      // the number 10 (base) is multipled
      // by itself. Math.pow(base, exponent)
      // Returns quotient of (10*10)^precision

      var power = Math.pow (10, precision);

      // This next function rounds up
      // (to the nearest whole integer)
      // the result of:
      // (size/1024/1024/1024...) * (10*10(*10....))

      var fullValue = Math.ceil (size * power);

      size = fullValue / power;
      return size + ' ' + sizes[unit];

    } else {
      return;
    }
  };
};
