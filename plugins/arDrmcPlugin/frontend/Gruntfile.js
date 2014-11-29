'use strict';

module.exports = function (grunt) {

  // Load tasks
  grunt.loadNpmTasks('grunt-contrib-jshint');
  grunt.loadNpmTasks('grunt-jscs');
  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-contrib-clean');
  grunt.loadNpmTasks('grunt-contrib-less');
  grunt.loadNpmTasks('grunt-contrib-copy');
  grunt.loadNpmTasks('grunt-contrib-concat');

  // Build task
  grunt.registerTask('default', [
    'lint'
  ]);

  // Build task
  grunt.registerTask('build', [
    'lint',
    'build-js',
    'build-css'
  ]);

  grunt.registerTask('build-css', [
    'less'
  ]);

  grunt.registerTask('build-js', [
    'clean:dist',
    'concat',
    'copy',
    'clean:build'
  ]);

  // This is for grunt-watch
  grunt.registerTask('build-js-after-changes', [
    'lint',
    'concat',
    'copy'
  ]);

  // Lint task
  grunt.registerTask('lint', [
    'jshint',
    'jscs'
  ]);

  grunt.initConfig({
    distdir: 'dist',
    builddir: 'dist/build',
    pkg: grunt.file.readJSON('package.json'),

    src: {
      js: [
        'Gruntfile.js',
        'app/scripts/**/*.js'
      ],
      jsEntry: [
        'app/scripts/app.js'
      ],
      less: [
        '../../arDominionPlugin/css/**/*.less'
      ]
    },

    watch: {
      js: {
        files: ['<%= src.js %>'],
        tasks: ['build-js-after-changes'],
        options: {
          spawn: false
        }
      },
      less: {
        files: ['<%= src.less %>'],
        tasks: ['build-css'],
        options: {
          spawn: false
        }
      }
    },

    jshint: {
      options: {
        jshintrc: '.jshintrc',
        reporter: require('jshint-stylish')
      },
      all: ['<%= src.js %>']
    },

    jscs: {
      options: {
        config: '.jscs.json'
      },
      files: {
        src: ['<%= src.js %>']
      }
    },

    less: {
      dev: {
        options: {
          relativeUrls: true
        },
        files: {
          '../../arDominionPlugin/css/min.css': '../../arDominionPlugin/css/main.less'
        }
      }
    },

    clean: {
      dist: ['<%= distdir %>/**/*'],
      build: ['<%= builddir %>/**/*']
    },

    concat: {
      vendor: {
        src: [
          'node_modules/wolfy87-eventemitter/EventEmitter.js',
          'node_modules/jquery/dist/jquery.js',
          'node_modules/d3/d3.js',
          'vendor/dagre.js',
          'node_modules/rickshaw/rickshaw.js',
          'node_modules/angular/angular.js',
          'node_modules/angular-ui-router/release/angular-ui-router.js',
          'vendor/angular-ui-router.js',
          'vendor/angular-ui.js',
          'node_modules/ng-storage/ngStorage.js',
          'vendor/angular-hotkeys/hotkeys.js',
          '../../../vendor/bootstrap/js/bootstrap.js'
        ],
        dest: '<%= builddir %>/vendor.js'
      },
      app: {
        src: [
          'app/scripts/app.js',
          'app/scripts/lib/cbd/graph.js',
          'app/scripts/lib/cbd/zoom.js',
          'app/scripts/lib/cbd/renderer.js',
          'app/scripts/lib/cbd/index.js',
          'app/scripts/**/module.js',
          'app/scripts/**/*.js'
        ],
        dest: '<%= builddir %>/app.js'
      }
    },

    copy: {
      build: {
        files: [{
          src: '<%= builddir %>/vendor.js',
          dest: '<%= distdir %>/<%= pkg.name %>.vendor.js'
        }, {
          src: '<%= builddir %>/app.js',
          dest: '<%= distdir %>/<%= pkg.name %>.app.js'
        }]
      }
    }

  });

};
