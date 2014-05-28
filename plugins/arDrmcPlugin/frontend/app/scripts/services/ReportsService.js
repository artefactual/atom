'use strict';

module.exports = function ($q, $timeout) {

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
        'name': 'Media and Performance Art',
        'id': 22,
        'type_id': 19,
        'type': 'Characteristic reports',
        'created_at': '1999-10-11',
        'description': 'All works added in a fiscal year',
        'results': [
          {
            'user': 'Ben',
            'aips_downloaded': 1,
            'files_downloaded': 4,
            'total_filesize': 361707,
            'last_modified': '2014-04-29',
            'created_at': '1999-10-11',
            'parent_artwork': {
              'id': 12345,
              'name': 'Semiotics of the Kitchen'
            }
          },
          {
            'user': 'Kate',
            'aips_downloaded': 2,
            'files_downloaded': 9,
            'total_filesize': 524000,
            'last_modified': '2014-03-29',
            'created_at': '1999-10-11',
            'parent_artwork': {
              'id': 345,
              'name': 'Official Welcome'
            }
          }
        ]
      },
      {
        'name': 'Architecture and Design',
        'id': 24,
        'type_id': 18,
        'type': 'Fixity report',
        'created_at': '1099-10-11',
        'description': 'All fixes on all buildings',
        'results': [
          {
            'The user': 'Ben',
            'aips_downloaded': 1,
            'files_downloaded': 4,
            'total_filesize': 550283,
            'last_modified': '2012-04-29',
            'created_at': '1989-07-10',
            'parent_artwork': {
              'Id': 3445,
              'Name': 'SimCity 2000'
            }
          },
          {
            'user': 'Kate',
            'aips_downloaded': 10,
            'files_downloaded': 59,
            'total_filesize': 54353,
            'last_modified': '2011-01-29',
            'created_at': '1800-01-20',
            'parent_artwork': {
              'id': 234,
              'Name': 'Space Invaders'
            }
          }
        ]
      }
    ]
  };

  var reportsView = {
    'savedOverview': {
      'run_from': 'Oct-Nov 2013',
      'report_start_date': '2013-10-01',
      'report_end_date': '2013-12-01',
      'saved_report_description': 'Download report for period during curation dept Review'
    },
    'savedReportsByDept': {
    // By department
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
            'total_filesize': 64000,
            'parent_artwork': 'Play Dead; Real Time'
          },
          {
            'table_total': true,
            'user': 2,
            'aips_downloaded': 4,
            'files_downloaded': 12,
            'total_filesize': 1143.3,
            'parent_artwork': 3
          }
        ]
      },
      'architecture_and_design_dept':
      {
        'dept_name': 'Architecture and Design',
        'results': [
          {
            'user': 'Ben',
            'aips_downloaded': 1,
            'files_downloaded': 4,
            'total_filesize': 351707,
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
          {
            'table_total': true,
            'user': 2,
            'aips_downloaded': 4,
            'files_downloaded': 12,
            'total_filesize': 1154.5,
            'parent_artwork': 4
          }
        ]
      },
      'all_depts_totals':
      {
        'dept_name': 'All',
        'results': [
          {
            'user_count': '2',
            'aips_downloaded': '8',
            'files_downloaded': '24',
            'total_filesize': '24555',
            'parent_artwork': '7'
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
          'total_filesize': 34333,
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
          'total_filesize': 66.69,
          'parent_work': 'Wendy and Lucy',
          'parent_department': 'Film'
        },
        // totals of two above
        {
          'table_total': true,
          'artwork_w_downloaded_mats': 4,
          'no_aips_downloaded': 2,
          'no_files_downloaded': 3,
          'total_filesize': 6686.66,
          'parent_department': 2
        }
      ],
      'results_all': [
        {
          'table_total': true,
          'artwork_w_downloaded_mats': 8,
          'no_aips_downloaded': 4,
          'no_files_downloaded': 11,
          'total_filesize': 1.55,
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
      console.log('async report');
      deferred.resolve(reportsBrowse);
    }, Math.random() * 100);
    return deferred.promise;
  };

  this.reportsViewData = function () {
    var deferred = $q.defer();

    $timeout(function () {
      console.log('async saved report');
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

};
