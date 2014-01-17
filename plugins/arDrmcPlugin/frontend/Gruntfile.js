module.exports = function (grunt) {

  // Load tasks
  grunt.loadNpmTasks('grunt-contrib-jshint');
  grunt.loadNpmTasks('grunt-contrib-watch');

  // Default task
  // grunt.registerTask('default', ['jshint']);

  // Print a timestamp (useful for when watching)
  grunt.registerTask('timestamp', function() {
    grunt.log.subhead(Date());
  });

  grunt.initConfig({
    distdir: 'dist',
    pkg: grunt.file.readJSON('package.json'),
    src: {
      js: ['app/scripts/app.js', 'app/scripts/services/*.js', 'app/scripts/lib/*.js', 'app/scripts/controllers/*.js', 'app/scripts/directives/*.js']
    },
    watch: {
      scripts: {
        files: ['<%= src.js %>'],
        tasks: ['timestamp']
      }
    },
    jshint: {
      files: ['Gruntfile.js', '<%= src.js %>'],
      options: {
        curly: true,
        eqeqeq: true,
        immed: true,
        latedef: true,
        newcap: true,
        noarg: true,
        sub: true,
        boss: true,
        eqnull: true,
        globals: {
          'jQuery': true,
          'Qubit': true,
          'angular': true,
          'window': true
        }
      }
    }
  });

};
