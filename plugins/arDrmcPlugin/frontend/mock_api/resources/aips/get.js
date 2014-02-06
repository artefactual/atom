var sortDir = (query.sort_direction) ? query.sort_direction : false,
    criteria = {},
    aipResults = [];

// apply optional limit
if (query.limit) {
  criteria.$limit = query.limit;
}

// apply optional sort
if (query.sort) {
  criteria.$sort = {};
  // sort by specified field and, optionally, by specified sort direction
  criteria.$sort[query.sort] = (sortDir && sortDir == 'desc') ? -1 : 1;
}

// TODO: move this class into a module
function ObjectPropertyTokenCounter(countOnlyProperties) {
  this.reset();
  this.countOnlyProperties = (countOnlyProperties) ? countOnlyProperties : false;
}

// TODO: maybe try to use Underscore's groupby for this
ObjectPropertyTokenCounter.prototype = {
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
          .toLowerCase()
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

function formatTokenCounts(tokenCounts) {
  var formatted = {'terms': []};

  for(var key in tokenCounts) {
    formatted.terms.push({
      "term": key,
      "count": tokenCounts[key]
    });
  }

  return formatted;
}

dpd.aipsraw.get(criteria, function(aips) {
  var overview = {'total': {'size': 0, 'count': 0}},
      classLowerCase;

  // calculate overview data
  aips.forEach(function(aip) {
    // add to total data
    overview['total']['size'] += aip.size;
    overview['total']['count'] += 1;

    // initialize classification data if needed
    classLowerCase = aip.class.toLowerCase();
    if (typeof overview[aip.class] == 'undefined') {
      overview[classLowerCase] = {'size': 0, 'count': 0};
    }

    // add to classification data    
    overview[classLowerCase]['size'] += aip.size;
    overview[classLowerCase]['count'] += 1;
  });

  // count occurrance of each value found in name property of each aip
  facetCounter = new ObjectPropertyTokenCounter(['class']);

  // process result set
  aips.forEach(function(aip) {
    // covert Deployd-created IDs to decimal IDs
    aip.id = parseInt(aip.id, 16);

    // work around Deployd issue with property names not allowing "_"
    aip.created_at = aip.createdat;
    delete aip.createdat;

    // add to results
    aipResults.push(aip);

    // add to facet counts
    facetCounter.count(aip);
  });

  var tmsData = {
    "accession_id": "1098.2005.a-c",
    "object_id": "100620",
    "title": "Play Dead; Real time",
    "date": "2003",
    "artist": "Douglas Gordon",
    "medium": "Three-channel video",
    "dimensions": "19:11 min, 14:44 min. (on larger screens), 21:58 min. (on monitor). Minimum Room Size: 24.8m x 13.07m",
    "description": "Exhibition materials: 3 DVD and players, 2 projectors, 3 monitor, 2 screens. The complete work is a three-screen piece, consisting of one retro projection, one front projection and one monitor. See file for installation instructions. One monitor and two projections on screens 19.69 X 11.38 feet. Viewer must be able to walk around screens."
  }

  var digitalObjectData = {
    "storage_total": "10776432223432",
    "related_total": {
        "digital_objects": 1,
        "aips": 12
    },
    "objects": {
        "artwork": {
            "total": 1,
            "total_size": "262453654232"
        },
        "documentation": {
            "total": 0,
            "total_size": "0"
        },
        "unclassified": {
            "total": 0,
            "total_size": "0"
        }
    }
  };

  // should result set
  setResult({
    'overview': overview,
    'aips': {
      'results': aipResults,
      'facets': {
        'class': formatTokenCounts(facetCounter.tokenCounts)
      }
    },
    'tms_metadata': tmsData,
    'digital_objects': digitalObjectData
  }); 
});
