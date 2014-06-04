'use strict';

module.exports = function () {

  return function (time) {

    if (!Number(time)) {
      return;
    }

    // Calculate
    var days = Math.floor(time / 86400);
    time -= days * 86400;
    var hours = Math.floor(time / 3600);
    time -= hours * 3600;
    var minutes = Math.floor(time / 60);
    time -= minutes * 60;
    var seconds = time;

    // Return
    if (days > 0) {
      return days + 'd ' + hours + 'h ' + minutes + 'm ' + seconds + 's';
    } else if (hours > 0) {
      return hours + 'h ' + minutes + 'm ' + seconds + 's';
    } else if (minutes > 0) {
      return minutes + 'm ' + seconds + 's';
    } else {
      return seconds + 's';
    }

  };

};
