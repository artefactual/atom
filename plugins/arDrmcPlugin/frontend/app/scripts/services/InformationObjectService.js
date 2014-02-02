'use strict';

module.exports = function ($q) {

  this.tree1 = [
    { id: 1, title: 'Play Dead; Real Time', level: 'work', children: [
      { id: 20, title: 'Components', level: 'description', children: [
        { id: 31, title: 'DVD', level: 'physical-component' },
        { id: 32, title: 'DVD', level: 'physical-component' },
        { id: 33, title: 'DVD', level: 'physical-component' },
        { id: 34, title: 'Digital Betacam', level: 'physical-component' },
        { id: 35, title: 'Digital Betacam', level: 'physical-component' },
        { id: 36, title: 'Digital Betacam', level: 'physical-component' },
        { id: 37, title: '.mov Uncompressed 10bit PAL', level: 'digital-object' },
        { id: 38, title: '.mov Uncompressed 10bit PAL', level: 'digital-object' },
        { id: 39, title: '.mov Uncompressed 10bit PAL', level: 'digital-object' },
        { id: 40, title: '.mov H264', level: 'digital-object' },
        { id: 41, title: '.mov H264', level: 'digital-object' },
        { id: 42, title: '.mov H264', level: 'digital-object' },
        { id: 43, title: '.mov H264', level: 'digital-object' },
        { id: 44, title: '.mov H264', level: 'digital-object' },
        { id: 45, title: '.mov H264', level: 'digital-object' },
        { id: 46, title: '.mov H264', level: 'digital-object' },
        { id: 47, title: '.mov H264', level: 'digital-object' },
        { id: 48, title: '.mov H264', level: 'digital-object' },
        { id: 49, title: '.mov H264', level: 'digital-object' },
        { id: 50, title: '.mov H264', level: 'digital-object' },
        { id: 51, title: '.mov H264', level: 'digital-object' }
      ]},
      { id: 11, title: 'Installation Documentation', level: 'description' }
    ]}
  ];

  this.tree2 = [
    { id: 2, title: 'Zidane, un portrait du 21e siècle', level: 'work', children: [
      { id: 20, title: 'Components', level: 'description', children: [
        { id: 31, title: 'DVD', level: 'physical-component' },
        { id: 32, title: 'DVD', level: 'physical-component' },
        { id: 33, title: 'DVD', level: 'physical-component' },
        { id: 34, title: 'Digital Betacam', level: 'physical-component' },
        { id: 35, title: 'Digital Betacam', level: 'physical-component' },
        { id: 36, title: 'Digital Betacam', level: 'physical-component' },
        { id: 37, title: '.mov Uncompressed 10bit PAL', level: 'digital-object' },
        { id: 38, title: '.mov Uncompressed 10bit PAL', level: 'digital-object' },
        { id: 39, title: '.mov Uncompressed 10bit PAL', level: 'digital-object' }
      ]}
    ]}
  ];

  this.works = {
    1: {
      id: 1,
      title: 'Play Dead; Real Time',
      tms: {
        accessionNumber: '1098.2005.a-c',
        objectId: '100620',
        title: 'Play Dead; Real Time',
        year: '2003',
        artist: 'Douglas Gordon',
        classification: 'Installation',
        medium: 'Three-channel video',
        dimensions: '19:11 min, 14:44 min. (on larger screens), 21:58 min. (on monitor). Minimum Room Size: 24.8m x 13.07m',
        description: 'Exhibition materials: 3 DVD and players, 2 projectors, 3 monitor, 2 screens. The complete work is a three-screen piece, consisting of one retro projection, one front projection and one monitor. See file for installation instructions. One monitor and two projections on screens 19.69 X 11.38 feet. Viewer must be able to walk around screens.'
      }
    },
    2: {
      id: 2,
      title: 'Zidane, un portrait du 21e siècle',
      tms: {
        accessionNumber: '1099.2006.a-c',
        objectId: '100621',
        title: 'Zidane, un portrait du 21e siècle',
        year: '2006',
        artist: 'Douglas Gordon',
        classification: 'Movie',
        medium: 'DVD',
        dimensions: 'Big enough',
        description: 'A movie about Zinedine Zidane'
      }
    }
  };

  this.getTree = function (id) {
    var deferred = $q.defer();

    if (this['tree' + id] !== undefined) {
      deferred.resolve(this['tree' + id]);
    } else {
      deferred.reject('There are not works currently available');
    }

    return deferred.promise;
  };

  this.getWorks = function () {
    var deferred = $q.defer();

    if (this.works !== undefined) {
      deferred.resolve(this.works);
    } else {
      deferred.reject('There are not works currently available');
    }

    return deferred.promise;
  };

  this.getWork = function (id) {
    var deferred = $q.defer();

    if (this.works[id] !== undefined) {
      deferred.resolve(this.works[id]);
    } else {
      deferred.reject('Work not found!');
    }

    return deferred.promise;
  };

};
