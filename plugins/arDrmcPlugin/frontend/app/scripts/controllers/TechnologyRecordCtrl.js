'use strict';

module.exports = function ($scope) {

  $scope.collection = [
    { id: 0, title: 'Codecs', level: 'Work', children: [
      { id: 100, title: 'Video Codecs', level: 'Expression', children: [
        { id: 120, title: 'Open Source', level: 'Manifestation', children: [
          { id: 130, title: 'FFmpeg', level: 'Component' },
          { id: 131, title: 'Xvid', level: 'Component', children: [
            { id: 140, title: 'Xvid v. 1.1', level: 'Digital Component' },
            { id: 141, title: 'Xvid v. 1.3.2', level: 'Digital Component' }
          ]},
          { id: 132, title: 'Schrodinger', level: 'Component' }
        ]},
        { id: 121, title: 'Proprietary', level: 'Manifestation' }
      ]},
      { id: 111, title: 'Audio Codecs', level: 'Expression' }
    ]}
  ];

};
