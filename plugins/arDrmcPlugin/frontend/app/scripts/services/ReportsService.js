'use strict';

module.exports = function ($q, $timeout) {

  var overviewData = {
    'overviewMockData':
    [
      {
        'name': 'Media and Performance Art',
        'id': 22,
        'type_id': 19,
        'type': 'Characteristic reports',
        'results': [
          {
            'user': 'Ben',
            'aips_downloaded': 1,
            'files_downloaded': 4,
            'total_filesize': 361707,
            'last_modified': '2014-04-29',
            'created_at': '1999-10-10',
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
        'name': 'Architecture and Design ',
        'id': 24,
        'type_id': 18,
        'type': 'Fixity report',
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

  var savedReportData = {
    'savedReportMockData':
    [
      {
        'name': 'The Dancing Troubadors',
        'id': 22,
        'type_id': 19,
        'type': 'Characteristic reports',
        'results': [
          {
            'user': 'Ben',
            'aips_downloaded': 1,
            'files_downloaded': 4,
            'total_filesize': 361707,
            'last_modified': '2014-04-29',
            'created_at': '1999-10-10',
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
        'name': 'Out of the Box Engineering Peritology',
        'id': 24,
        'type_id': 18,
        'type': 'Fixity report',
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

  this.asyncReportData = function () {
    var deferred = $q.defer();

    $timeout(function () {
      console.log('async report');
      deferred.resolve(overviewData);
    }, Math.random() * 6000);
    return deferred.promise;
  };

  this.asyncSavedReportData = function () {
    var deferred = $q.defer();

    $timeout(function () {
      console.log('async saved report');
      deferred.resolve(savedReportData);
    }, Math.random() * 2000);
    return deferred.promise;
  };

  // See InformationObjectService for more reference examples
  this.getAll = function () {
    return $q.all([
      this.asyncReportData(),
      this.asyncSavedReportData()
    ]).then(function (response) {
      return response;
    });
  };

};
