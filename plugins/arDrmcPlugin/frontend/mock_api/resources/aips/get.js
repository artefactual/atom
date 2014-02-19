var path = require('path'),
    helpers = require(path.join(process().cwd(), 'resources/aips/lib/helpers.js')),
    sortDir,
    criteria = {},
    filterFields = ['classification', 'class', 'uuid'],
    aipResults = [],
    synonyms = {
      'classification': 'class',
      'created_at': 'createdat',
      'class_id': 'classid'
    },
    value;

// apply optional skip
if (query.skip) {
  criteria.$skip = query.skip;
}

// apply optional limit
if (query.limit) {
  criteria.$limit = query.limit;
}

// apply optional sort
if (query.sort) {
  criteria.$sort = {};
  // sort by specified field and, optionally, by specified sort direction
  sortDir = (query.sort_direction) ? query.sort_direction : false;

  // normalize column names
  if (typeof synonyms[query.sort] != 'undefined') {
    query.sort = synonyms[query.sort];
  }
  criteria.$sort[query.sort] = (sortDir && sortDir == 'desc') ? -1 : 1;
}

filterFields.forEach(function(field) {
  if (typeof query[field] != 'undefined') {
    if (typeof synonyms[field] != 'undefined') {
      value = query[field];
      field = synonyms[field];
      query[field] = value;
    }
    criteria[field] = query[field];
  }
});

// fetch AIPs matching criteria
dpd.aipsraw.get(criteria, function(aips) {
  var classLowerCase;

  // process result set
  aips.forEach(function(aip) {
    // work around Deployd issue with property names not allowing "_"
    aip.created_at = aip.createdat;
    delete aip.createdat;

    aip.class_id = aip.classid;
    delete aip.classid;

    // add to results
    aipResults.push(aip);
  });

  // mock TMS data
  var tmsData = {
    "accession_id": "1098.2005.a-c",
    "object_id": "100620",
    "title": "Play Dead; Real time",
    "date": "2003",
    "artist": "Douglas Gordon",
    "medium": "Three-channel video",
    "dimensions": "19:11 min, 14:44 min. (on larger screens), 21:58 min. (on monitor). Minimum Room Size: 24.8m x 13.07m",
    "description": "Exhibition materials: 3 DVD and players, 2 projectors, 3 monitor, 2 screens. The complete work is a three-screen piece, consisting of one retro projection, one front projection and one monitor. See file for installation instructions. One monitor and two projections on screens 19.69 X 11.38 feet. Viewer must be able to walk around screens."
  };

  // mock digital object data
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

  // fetch all AIPs matching criteria to get found count
  delete criteria['$limit'];
  delete criteria['$skip'];
  dpd.aipsraw.get(criteria, function(aips) {
    // count occurrance of each value found in name property of each aip
    facetCounter = new helpers.ObjectPropertyTokenCounter(['class']);

    aips.forEach(function(aip) {
      // add to facet counts
      facetCounter.count(aip);
    });

    // set result data to send back as response
    setResult({
      'overview': helpers.calculateOverviewData(aips),
      'aips': {
        'results': aipResults,
        'facets': {
          'class': helpers.formatTokenCounts(facetCounter.tokenCounts)
        }
      },
      'tms_metadata': tmsData,
      'digital_objects': digitalObjectData
    });
  });
});
