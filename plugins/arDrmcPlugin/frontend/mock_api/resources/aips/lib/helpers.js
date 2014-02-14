/*

NOTE: after editing this file, you must restart Deployd to see the changes
reflected in your API.

*/

// calculate AIP overview data
exports.calculateOverviewData = function(aips) {
  var overview = {'total': {'size': 0, 'count': 0}},
      classLowerCase;

  // calculate overview data
  aips.forEach(function(aip) {
    // add to total data
    overview['total']['size'] += aip.size;
    overview['total']['count'] += 1;

    // initialize classification data if needed
    classCaseAdjusted = aip.class;
    if (typeof overview[classCaseAdjusted] == 'undefined') {
      overview[classCaseAdjusted] = {'size': 0, 'count': 0};
    }

    // add to classification data
    overview[classCaseAdjusted]['size'] += aip.size;
    overview[classCaseAdjusted]['count'] += 1;
  });

  return overview;
};

// class to help count token instances in object properties
exports.ObjectPropertyTokenCounter = function(countOnlyProperties) {
  this.reset();
  this.countOnlyProperties = (countOnlyProperties) ? countOnlyProperties : false;
}

exports.ObjectPropertyTokenCounter.prototype = {
  tokenCounts: {},

  reset: function() {
    this.tokenCounts = {};
  },

  count: function(object) {
    var self = this;

    for (var key in object) {
      if (!this.countOnlyProperties || (this.countOnlyProperties.indexOf(key) != -1)) {
        // split object value into tokens by whitespace
        values = object[key]
          .toString()
          .split(/[ ,]+/);

        // add each value to token count
        values.forEach(function(value) {
          if (typeof self.tokenCounts[value] == 'undefined') {
            self.tokenCounts[value] = 1;
          } else {
            self.tokenCounts[value]++;
          }
        });
      }
    }
  }
};

// format token counts
exports.formatTokenCounts = function(tokenCounts) {
  var formatted = {'terms': []};

  for(var key in tokenCounts) {
    formatted.terms.push({
      "term": key,
      "count": tokenCounts[key]
    });
  }

  return formatted;
}
