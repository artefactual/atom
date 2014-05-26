'use strict';

module.exports = function () {

  return function (seconds) {
    var minute = 60;
    var hour = minute * 60;

    var hours = Math.floor(seconds / hour);
    var minutes = Math.floor(seconds / minute);

    var remMinutes = seconds % hours;
    var remSeconds = seconds % minute;

    if (seconds < minute) {
      return seconds + 's';
    } else if (seconds > minute && seconds < hour) {
      // bigger than 1 minute, smaller than 1 hour
      return minutes + 'm' + remSeconds + 's';
    } else if (seconds > hour) {
      // bigger than 1 hour
      return hours + 'h' + remMinutes + 'm' + remSeconds + 's';
    } else if (!Number(seconds)) {
      // Not available
      return;
    }
  };

};
