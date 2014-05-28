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
    'savedReports': {
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
