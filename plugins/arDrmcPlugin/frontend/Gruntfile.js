'use strict';

module.exports = function (grunt) {

  /*
   * Some TODO items for this build:
   *  - Add more comments and a introduction
   *  - Add source maps for better debugging
   *  - Add basic testing
   *  - Remove libs from vendor/, use bower?
   *  - Compile CSS using recess?
   *  - More linting: better .jshintrc, jscs and csslint!
        See: http://goo.gl/pjQku0
   *  - Look at yeoman-angular, they have a bunch of nice features
   *  - Builder alternatives? (gulp)
   *  - Module bundler alternatives? requirejs, webpack...
   *    requirejs can fully run in a browser, but its syntax is not that nice!
   *    webpack seems to be awesome, see http://goo.gl/3pmIjy
   */

  // Load tasks
  grunt.loadNpmTasks('grunt-contrib-jshint');
  grunt.loadNpmTasks('grunt-jscs-checker');
  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-contrib-clean');
  grunt.loadNpmTasks('grunt-contrib-less');
  grunt.loadNpmTasks('grunt-contrib-copy');
  grunt.loadNpmTasks('grunt-contrib-concat');
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-browserify');

  // Build task
  grunt.registerTask('default', [
    'lint'
  ]);

  // Build task
  grunt.registerTask('build', [
    'clean:dist',
    'browserify',
    'concat',
    'less',
    'clean:build'
  ]);

  // Release task
  grunt.registerTask('release', [
    'build',
    'uglify'
  ]);

  // Lint task
  grunt.registerTask('lint', [
    'jshint',
    'jscs'
  ]);

  // Print a timestamp (useful for when watching)
  grunt.registerTask('timestamp', function () {
    grunt.log.subhead(Date());
  });

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
        tasks: ['timestamp', 'lint', 'build']
      },
      less: {
        files: ['<%= src.less %>'],
        tasks: ['timestamp', 'less']
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
          '../../arDominionPlugin/css/min.css': '../../arDominionPlugin/css/main.less' // '../../../arDominionPlugin/css/main.less'
        }
      }
    },

    clean: {
      dist: ['<%= distdir %>/**/*'],
      build: ['<%= builddir %>/**/*']
    },

    browserify: {

      // Here we are creating shims for browserify for the 3rd-party libraries
      // that doesn't have its corresponding package in npm or that we don't
      // to use for some reason (e.g. the maintainer doesn't update them).
      // Add in browserify.vendor.src all the libs you need but make sure that
      // you also declare the shim under browserify.vendor.options.shim.
      // There is a good example in: http://goo.gl/rbIFwu
      vendor: {
        src: [
          'vendor/angular-strap.js'
        ],
        dest: '<%= builddir %>/vendor-shims.js',
        options: {
          strap: {
            'angular-strap': {
              path: './vendor/angular-strap.js',
              exports: 'angular-strap'
            }
          }
        }
      },

      app: {
        src: ['<%= src.jsEntry %>'],
        dest: '<%= builddir %>/app.js',
        options: {
          external: [
            '9RiUY6' // Why? Mysterious! grunt-browserify: wtf?!
          ]
        }
      },

    },

    concat: {
      build: {
        src: [
          '<%= builddir %>/app.js'
        ],
        dest: '<%= distdir %>/<%= pkg.name %>.js'
      }
    },

    uglify: {
      options: { },
      relase: {
        files: {
          '<%= distdir %>/<%= pkg.name %>.min.js': [
            '<%= distdir %>/<%= pkg.name %>.js'
          ]
        }
      }
    }

  });

};
