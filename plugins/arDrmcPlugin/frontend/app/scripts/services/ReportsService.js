'use strict';

module.exports = function ($q, $timeout, $http, SETTINGS) {

  var reportsBrowse = {
    'browseOverview': {
      'last_report_added_date': '1954-11-01T00:06:10Z',
      'last_report_added-name': 'A report about memes and their relationship to mimes',
      'activity_reports': 0,
      'fixity_reports': 1,
      'characteristic_reports': 1
    },
    'browseSamples':
    [
      {
        'id': 400,
        'name': 'Media and performance Art',
        'type': 'high_level_ingest',
        'created_at': '2014-02-08T18:03:42Z',
        'description': 'All works added in a fiscal year'
      },
      {
        'id': 401,
        'name': 'Architecture and design',
        'type': 'granular_ingest',
        'created_at': '2014-02-08T08:03:42Z',
        'description': 'All fixes on all buildings'
      }
    ]
  };

  var reportsView = {
    'savedOverview': {
      'report_type': 'Amount downloaded',
      'run_from': '2010-02-08T18:03:42Z',
      'report_start_date': '2013-12-10T10:23:12Z',
      'report_end_date': '2014-01-08T19:45:30Z',
      'saved_report_description': 'Download report for period during curation department review'
    },
    'savedReportsByDept': {
      'media_and_performance_dept':
      {
        'dept_name': 'Media and Performance Art',
        'results': [
          {
            'user': 'Ben',
            'aips_downloaded': 1,
            'files_downloaded': 4,
            'total_filesize': 361707,
            'parent_artwork': 'Semiotics of the Kitchen'
          },
          {
            'user': 'Ben',
            'aips_downloaded': 1,
            'files_downloaded': 4,
            'total_filesize': 561707,
            'parent_artwork': 'Official Welcome'
          },
          {
            'user': 'Kate',
            'aips_downloaded': 2,
            'files_downloaded': 9,
            'total_filesize': 524000,
            'parent_artwork': 'Official Welcome'
          },
          {
            'user': 'Kate',
            'aips_downloaded': 0,
            'files_downloaded': 4,
            'total_filesize': 640020,
            'parent_artwork': 'Play Dead; Real Time'
          }
        ],
        'overview': {
          'user': 2,
          'aips_downloaded': 4,
          'files_downloaded': 12,
          'total_filesize': 115911433,
          'parent_artwork': 3
        }
      },
      'architecture_and_design_dept':
      {
        'results': [
          {
            'user': 'Ben',
            'aips_downloaded': 1,
            'files_downloaded': 4,
            'total_filesize': 32251707,
            'parent_artwork': 'Sim City 2000'
          },
          {
            'user': 'Ben',
            'aips_downloaded': 1,
            'files_downloaded': 4,
            'total_filesize': 551707,
            'parent_artwork': 'Space Invaders'
          },
          {
            'user': 'Kate',
            'aips_downloaded': 2,
            'files_downloaded': 0,
            'total_filesize': 524000,
            'parent_artwork': 'Tetris'
          },
          {
            'user': 'Kate',
            'aips_downloaded': 0,
            'files_downloaded': 4,
            'total_filesize': 44000,
            'parent_artwork': 'The Sims'
          },
        ],
        'overview': {
          'user': 2,
          'aips_downloaded': 8,
          'files_downloaded': 24,
          'total_filesize': 245433,
          'parent_artwork': 7
        }
      },
      'all_depts_totals':
      {
        'results': [
          {
            'user_count': 2,
            'aips_downloaded': 8,
            'files_downloaded': 24,
            'total_filesize': 229444555,
            'parent_artwork': 7
          }
        ]
      }
    },
    'savedReportsByUser': {
      'results_ben': [
        {
          'artwork_w_downloaded_mats': 2,
          'no_aips_downloaded': 1,
          'no_files_downloaded': 4,
          'total_filesize': 324333,
          'parent_work': 'Semiotics of the Kitchen',
          'parent_department': 'Media and Performance Art'
        },
        {
          'artwork_w_downloaded_mats': 2,
          'no_aips_downloaded': 1,
          'no_files_downloaded': 4,
          'total_filesize': 534333,
          'parent_work': 'Space Invaders',
          'parent_department': 'Architecture and Design'
        },
        // totals of two above
        {
          'table_total': true,
          'artwork_w_downloaded_mats': 4,
          'no_aips_downloaded': 2,
          'no_files_downloaded': 8,
          'total_filesize': 568666,
          'parent_department': 2
        }
      ],
      'results_kate': [
        {
          'artwork_w_downloaded_mats': 2,
          'no_aips_downloaded': 2,
          'no_files_downloaded': 0,
          'total_filesize': 600,
          'parent_work': 'where\'s my fucking peanut',
          'parent_department': 'Prints and Illustrated Books'
        },
        {
          'artwork_w_downloaded_mats': 2,
          'no_aips_downloaded': 0,
          'no_files_downloaded': 3,
          'total_filesize': 6669,
          'parent_work': 'Wendy and Lucy',
          'parent_department': 'Film'
        },
        // totals of two above
        {
          'table_total': true,
          'artwork_w_downloaded_mats': 4,
          'no_aips_downloaded': 2,
          'no_files_downloaded': 3,
          'total_filesize': 668666,
          'parent_department': 2
        }
      ],
      'results_all': [
        {
          'table_total': true,
          'artwork_w_downloaded_mats': 8,
          'no_aips_downloaded': 4,
          'no_files_downloaded': 11,
          'total_filesize': 152225,
          'parent_department': 4
        }
      ]
    }
  };

  // Remove $q.all when endpoints ready. No need
  // for chained loading
  this.reportsBrowseData = function () {
    var deferred = $q.defer();

    $timeout(function () {
      deferred.resolve(reportsBrowse);
    }, Math.random() * 100);
    return deferred.promise;
  };

  this.reportsViewData = function () {
    var deferred = $q.defer();

    $timeout(function () {
      deferred.resolve(reportsView);
    }, Math.random() * 500);
    return deferred.promise;
  };

  // See InformationObjectService for more reference examples
  this.getAll = function () {
    return $q.all([
      this.reportsBrowseData(),
      this.reportsViewData()
    ]).then(function (response) {
      return response;
    });
  };

  this.generateReport = function (data) {
    var configuration = {
      method: 'POST',
      url: SETTINGS.frontendPath + 'api/report'
    };

    if (angular.isDefined(data)) {
      console.log('/data',data);
      configuration.data = data;
    }

    // Only required if using GET!
    // Convert range object into a flat pair of params: from and to
    // if (angular.isDefined(params.range)) {
    //  params.from = params.range.from;
    //  params.to = params.range.to;
    //  delete params.range;
    // }

    return $http(configuration);
  };
};
