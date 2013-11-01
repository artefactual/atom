// HEATHER'S DEV VERSION -- trying to add more nodes
// 'use strict';

// angular.module('momaApp')
//   .controller('TestCtrl', function ($scope, $http, atomGlobals) {

//     $scope.atomGlobals = atomGlobals;

//     $scope.collection = [
//       { id: 0, title: 'Zero', level: 'Work', children: [
//         { id: 1, title: 'One', level: 'Expression' }, //end
//         { id: 2, title: 'Two', level: 'Expression', children:
//           [
//             { id: 3, title: 'Three', level: 'Manifestation', children:
//               [
//                 { id: 4, title: 'Four', level: 'Component' },
//                 { id: 5, title: 'Five', level: 'Component' },
//                 { id: 6, title: 'Six', level: 'Component' },
//                 { id: 7, title: 'Seven', level: 'Component' },
//                 { id: 8, title: 'Eight', level: 'Component' }
//               ]
//              },
//             { id: 9, title: 'Nine', level: 'Manifestation', children:
//               [
//                 { id: 10, title: 'Ten', level: 'Component' }
//               ]
//             } //9
//           ] // expression
//         } //2
//       } //0
//     ]; //scope
//   ];
// }); //controller


// WORKING VERSION
'use strict';

angular.module('momaApp')
  .controller('TestCtrl', function ($scope, $http, atomGlobals) {

    $scope.atomGlobals = atomGlobals;

    $scope.collection = [
      { id: 0, title: 'Zero', level: 'Work', children: [
        { id: 1, title: 'One', level: 'Expression', children:
          [
            { id: 2, title: 'Two', level: 'Manifestation' },
            { id: 3, title: 'Three', level: 'Manifestation' },
          ]
        },
        { id: 4, title: 'Four', level: 'Expression' },
        { id: 5, title: 'Five', level: 'Expression' },
        { id: 6, title: 'Six', level: 'Expression', children:
          [
            { id: 7, title: 'Seven', level: 'Manifestation' },
            { id: 8, title: 'Eight', level: 'Manifestation' },
          ]}
      ]}
    ];

  });
