'use strict';

module.exports = function () {

  return function (input) {
    var seconds = input;

    if (input < 60) {
      return (seconds + 's');
    } else if (!Number(input)) {
      return 'none';
    } else {
      var minutes = Math.floor(seconds / 60);
      var remSeconds = seconds % 60;

      var total = minutes + 'm' + remSeconds + 's';
      return total;
    }
  };
};
