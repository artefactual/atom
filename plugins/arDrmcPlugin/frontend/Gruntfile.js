'use strict';

module.exports = function (grunt) {

  // Load tasks
  grunt.loadNpmTasks('grunt-contrib-jshint');
  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-contrib-clean');
  grunt.loadNpmTasks('grunt-contrib-copy');

  // Default task
  grunt.registerTask('default', ['jshint', 'build']);
  grunt.registerTask('build', ['clean', 'concat', 'recess:build', 'copy:assets']);

  // Print a timestamp (useful for when watching)
  grunt.registerTask('timestamp', function() {
    grunt.log.subhead(Date());
  });

  grunt.initConfig({
    distdir: 'dist',
    pkg: grunt.file.readJSON('package.json'),
    src: {
      js: [
        'app/scripts/app.js',
        'app/scripts/services/*.js',
        'app/scripts/lib/*.js',
        'app/scripts/controllers/*.js',
        'app/scripts/directives/*.js'
      ]
    },
    watch: {
      scripts: {
        files: ['<%= src.js %>'],
        tasks: ['timestamp']
      }
    },
    jshint: {
      options: {
        jshintrc: '.jshintrc',
        reporter: require('jshint-stylish')
      },
      all: [
        'Gruntfile.js',
        '<%= src.js %>'
      ]
    },
    clean: ['<%= distdir %>/*'],
    copy: {
      assets: {
        files: [
          { dest: '<%= distdir %>', src: '**', expand: true, cwd: 'src/assets/' }
        ]
      }
    },
  });

};
