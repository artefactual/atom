'use strict';

module.exports = function () {

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
        { id: 42, title: '.mov H264', level: 'digital-object' }
      ]},
      { id: 11, title: 'Installation Documentation', level: 'description' }
    ]}
  ];

  this.tree2 = [
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
        { id: 39, title: '.mov Uncompressed 10bit PAL', level: 'digital-object' }
      ]}
    ]}
  ];

  this.getTree = function (id) {
    return this['tree' + id];
  };

};
