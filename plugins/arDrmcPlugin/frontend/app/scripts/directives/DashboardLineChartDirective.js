'use strict';

var myrickshaw = require('rickshaw');

module.exports = function (StatisticsService) {
  return {
    restrict: 'E',
    scope: {
    },
    link: function (scope, element, attrs) {
      var service;

      var palette = new myrickshaw.Color.Palette();
      // the D3 bits...
      var dataset = [];
      switch (attrs.chartName) {
        case 'artwork-sizes':
          service = StatisticsService.getArtworkSizesByYearSummary();
          dataset = [
            {
              name: 'Average GB per artwork',
              data: [ { x: 1975, y: 34.8450063069661 }, { x: 1976, y: 0 }, { x: 1977, y: 0 }, { x: 1978, y: 0 }, { x: 1979, y: 58.7409553527832 }, { x: 1980, y: 93.5442428588867 }, { x: 1981, y: 32.3523600260417 }, { x: 1982, y: 42.2530128479004 }, { x: 1983, y: 14.4835834503174 }, { x: 1984, y: 49.6120316641671 }, { x: 1985, y: 51.5810508728027 }, { x: 1986, y: 58.7672443389893 }, { x: 1987, y: 60.5879144668579 }, { x: 1988, y: 77.4421558380127 }, { x: 1989, y: 70.0650726318359 }, { x: 1990, y: 58.552698135376 }, { x: 1991, y: 29.1906859079997 }, { x: 1992, y: 89.6675796508789 }, { x: 1993, y: 50.0719778878348 }, { x: 1994, y: 33.0956865946452 }, { x: 1995, y: 97.2998397827148 }, { x: 1996, y: 244.696655273437 }, { x: 1997, y: 55.6301851272583 }, { x: 1998, y: 59.9676860809326 }, { x: 1999, y: 44.1539192199707 }, { x: 2000, y: 37.7542266845703 }, { x: 2001, y: 0.595066070556641 }, { x: 2002, y: 25.3027973175049 }, { x: 2003, y: 47.4875679016113 }, { x: 2004, y: 34.0838386134097 }, { x: 2005, y: 20.4515752156575 }, { x: 2006, y: 42.4154425726996 }, { x: 2007, y: 29.2149564107259 }, { x: 2008, y: 28.1857687528016 }, { x: 2009, y: 35.5505867004395 }, { x: 2010, y: 50.4169177246094 }, { x: 2011, y: 80.9254245967655 }, { x: 2012, y: 109.05777648001 }, { x: 2013, y: 68.4722938537598 } ],
              color: palette.color()
            },
            {
              name: 'Median GB per artwork',
              data: [ { x: 1975, y: 19.7568893432617 }, { x: 1976, y: 0 }, { x: 1977, y: 0 }, { x: 1978, y: 0 }, { x: 1979, y: 52.6265296936035 }, { x: 1980, y: 93.5442428588867 }, { x: 1981, y: 29.864128112793 }, { x: 1982, y: 35.1670455932617 }, { x: 1983, y: 12.6840934753418 }, { x: 1984, y: 37.5213508605957 }, { x: 1985, y: 46.0741310119629 }, { x: 1986, y: 42.6051483154297 }, { x: 1987, y: 45.99560546875 }, { x: 1988, y: 64.2804641723633 }, { x: 1989, y: 67.2664413452148 }, { x: 1990, y: 32.8273544311523 }, { x: 1991, y: 18.2656707763672 }, { x: 1992, y: 0.0320472717285156 }, { x: 1993, y: 43.5171203613281 }, { x: 1994, y: 21.7949104309082 }, { x: 1995, y: 100.32942199707 }, { x: 1996, y: 244.696655273437 }, { x: 1997, y: 67.5222930908203 }, { x: 1998, y: 60.3448448181152 }, { x: 1999, y: 44.1539192199707 }, { x: 2000, y: 39.7548980712891 }, { x: 2001, y: 0.595066070556641 }, { x: 2002, y: 22.5301551818848 }, { x: 2003, y: 14.2280616760254 }, { x: 2004, y: 5.16804504394531 }, { x: 2005, y: 1.64801025390625 }, { x: 2006, y: 0.586860656738281 }, { x: 2007, y: 5.52630615234375 }, { x: 2008, y: 0.721138000488281 }, { x: 2009, y: 1.58941650390625 }, { x: 2010, y: 0.001617431640625 }, { x: 2011, y: 41.9939651489258 }, { x: 2012, y: 16.9408111572266 }, { x: 2013, y: 1.26030349731445 } ],
              color: palette.color()
            }
          ];
          break;

        case 'monthly-total-codec':
          //service = StatisticsService.getMonthlyTotalByCodec();
          dataset = [
            {
              name: 'Uncompressed 10-bit',
              data: [ { x: 41365, y: 5 }, { x: 41395, y: 5 }, { x: 41426, y: 6 }, { x: 41456, y: 7 }, { x: 41487, y: 8 }, { x: 41518, y: 5 }, { x: 41548, y: 5 }, { x: 41579, y: 5 }, { x: 41609, y: 4 }, { x: 41640, y: 6 }, { x: 41671, y: 8 }, { x: 41699, y: 5 } ],
              color: palette.color()
            },
            {
              name: 'apple prores 422',
              data: [ { x: 41365, y: 3 }, { x: 41395, y: 2 }, { x: 41426, y: 2 }, { x: 41456, y: 2 }, { x: 41487, y: 3 }, { x: 41518, y: 3 }, { x: 41548, y: 4 }, { x: 41579, y: 4 }, { x: 41609, y: 5 }, { x: 41640, y: 3 }, { x: 41671, y: 3 }, { x: 41699, y: 3 } ],
              color: palette.color()
            },
            {
              name: 'H264',
              data: [ { x: 41365, y: 5 }, { x: 41395, y: 5 }, { x: 41426, y: 5 }, { x: 41456, y: 5 }, { x: 41487, y: 6 }, { x: 41518, y: 6 }, { x: 41548, y: 8 }, { x: 41579, y: 9 }, { x: 41609, y: 6 }, { x: 41640, y: 5 }, { x: 41671, y: 6 }, { x: 41699, y: 7 } ],
              color: palette.color()
            },
            {
              name: 'Animation',
              data: [ { x: 41365, y: 4 }, { x: 41395, y: 4 }, { x: 41426, y: 3 }, { x: 41456, y: 3 }, { x: 41487, y: 3 }, { x: 41518, y: 2 }, { x: 41548, y: 3 }, { x: 41579, y: 2 }, { x: 41609, y: 2 }, { x: 41640, y: 2 }, { x: 41671, y: 1 }, { x: 41699, y: 1 } ],
              color: palette.color()
            },
            {
              name: 'MPEG-2',
              data: [ { x: 41365, y: 6 }, { x: 41395, y: 6 }, { x: 41426, y: 6 }, { x: 41456, y: 5 }, { x: 41487, y: 5 }, { x: 41518, y: 4 }, { x: 41548, y: 4 }, { x: 41579, y: 3 }, { x: 41609, y: 2 }, { x: 41640, y: 1 }, { x: 41671, y: 1 }, { x: 41699, y: 1 } ],
              color: palette.color()
            },
            {
              name: 'FFV!',
              data: [ { x: 41365, y: 0 }, { x: 41395, y: 0 }, { x: 41426, y: 0 }, { x: 41456, y: 0 }, { x: 41487, y: 4 }, { x: 41518, y: 5 }, { x: 41548, y: 5 }, { x: 41579, y: 6 }, { x: 41609, y: 7 }, { x: 41640, y: 8 }, { x: 41671, y: 9 }, { x: 41699, y: 10 } ],
              color: palette.color()
            },
            {
              name: 'DV',
              data: [ { x: 41365, y: 8 }, { x: 41395, y: 8 }, { x: 41426, y: 7 }, { x: 41456, y: 6 }, { x: 41487, y: 5 }, { x: 41518, y: 4 }, { x: 41548, y: 4 }, { x: 41579, y: 4 }, { x: 41609, y: 3 }, { x: 41640, y: 2 }, { x: 41671, y: 2 }, { x: 41699, y: 2 } ],
              color: palette.color()
            }
          ];
          break;

        case 'monthly-total-formats':
          //service = StatisticsService.getMonthlyTotalByCodec();
          dataset = [
            {
              name: 'MOV',
              data: [ { x: 41365, y: 5 }, { x: 41395, y: 5 }, { x: 41426, y: 6 }, { x: 41456, y: 7 }, { x: 41487, y: 8 }, { x: 41518, y: 5 }, { x: 41548, y: 5 }, { x: 41579, y: 5 }, { x: 41609, y: 4 }, { x: 41640, y: 6 }, { x: 41671, y: 8 }, { x: 41699, y: 5 } ],
              color: palette.color()
            },
            {
              name: 'FFV1',
              data: [ { x: 41365, y: 3 }, { x: 41395, y: 2 }, { x: 41426, y: 2 }, { x: 41456, y: 2 }, { x: 41487, y: 3 }, { x: 41518, y: 3 }, { x: 41548, y: 4 }, { x: 41579, y: 4 }, { x: 41609, y: 5 }, { x: 41640, y: 3 }, { x: 41671, y: 3 }, { x: 41699, y: 3 } ],
              color: palette.color()
            },
            {
              name: 'H264',
              data: [ { x: 41365, y: 5 }, { x: 41395, y: 5 }, { x: 41426, y: 5 }, { x: 41456, y: 5 }, { x: 41487, y: 6 }, { x: 41518, y: 6 }, { x: 41548, y: 8 }, { x: 41579, y: 9 }, { x: 41609, y: 6 }, { x: 41640, y: 5 }, { x: 41671, y: 6 }, { x: 41699, y: 7 } ],
              color: palette.color()
            },
            {
              name: 'PDF',
              data: [ { x: 41365, y: 4 }, { x: 41395, y: 4 }, { x: 41426, y: 10 }, { x: 41456, y: 3 }, { x: 41487, y: 3 }, { x: 41518, y: 15 }, { x: 41548, y: 3 }, { x: 41579, y: 2 }, { x: 41609, y: 2 }, { x: 41640, y: 12 }, { x: 41671, y: 1 }, { x: 41699, y: 1 } ],
              color: palette.color()
            },
            {
              name: 'TXT',
              data: [ { x: 41365, y: 4 }, { x: 41395, y: 3 }, { x: 41426, y: 2 }, { x: 41456, y: 1 }, { x: 41487, y: 5 }, { x: 41518, y: 2 }, { x: 41548, y: 20 }, { x: 41579, y: 42 }, { x: 41609, y: 10 }, { x: 41640, y: 5 }, { x: 41671, y: 0 }, { x: 41699, y: 4 } ],
              color: palette.color()
            },
            {
              name: '.EXE',
              data: [ { x: 41365, y: 1 }, { x: 41395, y: 2 }, { x: 41426, y: 3 }, { x: 41456, y: 22 }, { x: 41487, y: 15 }, { x: 41518, y: 5 }, { x: 41548, y: 5 }, { x: 41579, y: 6 }, { x: 41609, y: 0 }, { x: 41640, y: 8 }, { x: 41671, y: 9 }, { x: 41699, y: 35 } ],
              color: palette.color()
            },
          ];
          break;

        case 'running-total-codec':
          //service = StatisticsService.getRunningTotalByCodec();
          dataset = [
            {
              name: 'Uncompressed 10-bit',
              data: [ { x: 41365, y: 5 }, { x: 41395, y: 10 }, { x: 41426, y: 16 }, { x: 41456, y: 23 }, { x: 41487, y: 31 }, { x: 41518, y: 36 }, { x: 41548, y: 41 }, { x: 41579, y: 46 }, { x: 41609, y: 52 }, { x: 41640, y: 58 }, { x: 41671, y: 66 }, { x: 41699, y: 71 } ],
              color: palette.color()
            },
            {
              name: 'apple prores 422',
              data: [ { x: 41365, y: 3 }, { x: 41395, y: 5 }, { x: 41426, y: 7 }, { x: 41456, y: 9 }, { x: 41487, y: 12 }, { x: 41518, y: 15 }, { x: 41548, y: 19 }, { x: 41579, y: 23 }, { x: 41609, y: 28 }, { x: 41640, y: 31 }, { x: 41671, y: 34 }, { x: 41699, y: 37 } ],
              color: palette.color()
            },
            {
              name: 'H264',
              data: [ { x: 41365, y: 5 }, { x: 41395, y: 10 }, { x: 41426, y: 15 }, { x: 41456, y: 20 }, { x: 41487, y: 26 }, { x: 41518, y: 32 }, { x: 41548, y: 41 }, { x: 41579, y: 47 }, { x: 41609, y: 53 }, { x: 41640, y: 59 }, { x: 41671, y: 66 }, { x: 41699, y: 73 } ],
              color: palette.color()
            },
            {
              name: 'Animation',
              data: [ { x: 41365, y: 6 }, { x: 41395, y: 10 }, { x: 41426, y: 13 }, { x: 41456, y: 16 }, { x: 41487, y: 19 }, { x: 41518, y: 21 }, { x: 41548, y: 23 }, { x: 41579, y: 25 }, { x: 41609, y: 27 }, { x: 41640, y: 28 }, { x: 41671, y: 29 }, { x: 41699, y: 30 } ],
              color: palette.color()
            },
            {
              name: 'MPEG-2',
              data: [ { x: 41365, y: 7 }, { x: 41395, y: 13 }, { x: 41426, y: 19 }, { x: 41456, y: 24 }, { x: 41487, y: 29 }, { x: 41518, y: 33 }, { x: 41548, y: 36 }, { x: 41579, y: 38 }, { x: 41609, y: 40 }, { x: 41640, y: 41 }, { x: 41671, y: 42 }, { x: 41699, y: 43 } ],
              color: palette.color()
            },
            {
              name: 'FFV!',
              data: [ { x: 41365, y: 8 }, { x: 41395, y: 8 }, { x: 41426, y: 8 }, { x: 41456, y: 8 }, { x: 41487, y: 12 }, { x: 41518, y: 17 }, { x: 41548, y: 23 }, { x: 41579, y: 30 }, { x: 41609, y: 37 }, { x: 41640, y: 46 }, { x: 41671, y: 56 }, { x: 41699, y: 66 } ],
              color: palette.color()
            },
            {
              name: 'DV',
              data: [ { x: 41365, y: 9 }, { x: 41395, y: 17 }, { x: 41426, y: 24 }, { x: 41456, y: 30 }, { x: 41487, y: 35 }, { x: 41518, y: 39 }, { x: 41548, y: 43 }, { x: 41579, y: 46 }, { x: 41609, y: 49 }, { x: 41640, y: 51 }, { x: 41671, y: 53 }, { x: 41699, y: 55 } ],
              color: palette.color()
            }
          ];
          break;
      }
      /*
      service.then(function (responses) {
        console.log(responses);
        dataset = responses.data.results;
      });
      */

      var max = Number.MIN_VALUE;
      for (var i = 0; i < dataset.length; i++) {
        for (var j = 0; j < dataset[i].data.length; j++) {
          max = Math.max(max, dataset[i].data[j].y);
        }
      }
      // round up to 10th
      max = Math.ceil(max / 10) * 10;

      var graph = new myrickshaw.Graph({
        element: element.find('rs-chart')[0],
        width: attrs.width,
        height: attrs.height,
        series: dataset,
        max: max
      });
      var xAxis = new myrickshaw.Graph.Axis.X({
        graph: graph,
        element: element.find('rs-x-axis')[0],
        orientation: 'bottom',
        pixelsPerTick: attrs.xperTick
      });

      xAxis.render();

      var yAxis = new myrickshaw.Graph.Axis.Y({
        graph: graph,
        element: element.find('rs-y-axis')[0],
        pixelsPerTick: attrs.yperTick,
        orientation: 'left',
        tickFormat: myrickshaw.Fixtures.Number.formatKMBT
      });
      yAxis.render();

      var legend = new myrickshaw.Graph.Legend({
        graph: graph,
        element: element.find('rs-legend')[0]
      });
      legend.render();

      graph.setRenderer('line');
      graph.render();
    }
  };
};
