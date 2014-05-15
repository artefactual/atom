'use strict';

module.exports = function ($q, $timeout) {

  var resolution = {
    'mockData':
    [
      {
        'name': 'Media and Performance Art',
        'results': [
          {
            'The user': 'Ben',
            'aips_downloaded': 1,
            'files_downloaded': 4,
            'Total_filesize': 361707,
            'parent_artwork': {
              'Id': 12345,
              'name': 'Semiotics of the Kitchen'
            }
          },
          {
            'user': 'Kate',
            'aips_downloaded': 2,
            'files_downloaded': 9,
            'Total_filesize': 524000,
            'parent_artwork': {
              'id': 345,
              'Name': 'Official Welcome'
            }
          }
        ]
      },
      {
        'name': 'Architecture and Design ',
        'results': [
          {
            'The user': 'Ben',
            'aips_downloaded': 1,
            'files_downloaded': 4,
            'Total_filesize': 550283,
            'parent_artwork': {
              'Id': 3445,
              'Name': 'SimCity 2000'
            }
          },
          {
            'user': 'Kate',
            'aips_downloaded': 10,
            'files_downloaded': 59,
            'Total_filesize': 54353,
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
      deferred.resolve(resolution);
    }, 2000);
    return deferred.promise;
  };

};
