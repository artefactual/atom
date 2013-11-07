'use strict';

angular.module('momaApp')
  .controller('Test2Ctrl', function ($scope, $http, atomGlobals) {

    $scope.atomGlobals = atomGlobals;

    $scope.collection = [
      { id: 0, title: 'Screen Test:<br />Ivy Nicholson', level: 'Work', children: [
        { id: 10, title: 'Retouched Film', level: 'Expression', children:
          [
            { id: 20, title: 'Exhibition Documentation', level: 'Manifestation', children:
              [
                { id: 30, title: 'Exhibition<br />Walkthrough', level: 'Component' }
              ]
            }
          ]
        },
        { id: 11, title: 'Original Film', level: 'Expression', children:
          [
            { id: 21, title: 'Artwork<br />Components', level: 'Manifestation', children:
              [
                 { id: 31, title: 'Reversal positive "original"', level: 'Component' },
                 { id: 32, title: 'Internegative', level: 'Component' },
                 { id: 33, title: 'Print', level: 'Component' },
                 { id: 34, title: 'Print', level: 'Component' },
                 { id: 35, title: 'DPX  Scan', level: 'Component' }
              ]
            }
          ]}
      ]}
    ];

});

function switchData(){

      var obj = document.getElementById('node-10');
      var a1  = document.getElementById('aside1');
      var a2  = document.getElementById('aside2');
      console.log(obj + a1 + a1 + 'Hi from heather');

      obj.onclick = onClick;

     function onClick(){
      a1.css("display", "none");
    };
  };

