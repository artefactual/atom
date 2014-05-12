'use strict';

module.exports = function (grunt) {

  // Load tasks
  grunt.loadNpmTasks('grunt-contrib-jshint');
  grunt.loadNpmTasks('grunt-jscs-checker');
  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-contrib-clean');
  grunt.loadNpmTasks('grunt-contrib-less');
  grunt.loadNpmTasks('grunt-contrib-copy');
  grunt.loadNpmTasks('grunt-contrib-concat'); // Not used
  grunt.loadNpmTasks('grunt-contrib-uglify'); // Not used
  grunt.loadNpmTasks('grunt-browserify');
  grunt.loadNpmTasks('grunt-karma');

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
    'less',
  ]);

  grunt.registerTask('build-js', [
    'clean:dist',
    'browserify',
    'copy',
    'clean:build'
  ]);

  // This is for grunt-watch
  grunt.registerTask('build-js-after-changes', [
    'lint',
    'browserify:app',
    'copy',
  ]);

  // Lint task
  grunt.registerTask('lint', [
    'jshint',
    'jscs'
  ]);

  var karmaConfig = function (configFile, customOptions) {
    var options = {
      configFile: configFile,
      keepalive: true
    };
    return grunt.util._.extend(options, customOptions);
  };

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

    karma: {
      unit: {
        options: karmaConfig('test/config/karma.config.js')
      },
      watch: {
        options: karmaConfig('test/config/karma.config.js', {
          singleRun: false,
          autoWatch: true
        })
      }
    },

    clean: {
      dist: ['<%= distdir %>/**/*'],
      build: ['<%= builddir %>/**/*']
    },

    browserify: {

      vendor: {
        src: 'app/scripts/import.js',
        dest: '<%= builddir %>/vendor.js',
        options: {
          debug: true,
          alias: [
            'jquery:jquery',
            'd3:d3',
            'rickshaw:rickshaw',
            'dagre:dagre',
            'angular:angular',
            'ui-router:ui-router',
            'wolfy87-eventemitter:wolfy87-eventemitter'
          ]
        }
      },

      app: {
        src: '<%= src.jsEntry %>',
        dest: '<%= builddir %>/app.js',
        options: {
          debug: true,
          external: [
            'jquery',
            'd3',
            'rickshaw',
            'dagre',
            'angular',
            'ui-router',
            'wolfy87-eventemitter'
          ]
        }
      },

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
