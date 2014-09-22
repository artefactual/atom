var gulp =  require('gulp'),
    watch = require('gulp-watch'),
    less = require('gulp-less');

gulp.task('default', ['compile-less']);

gulp.task('watch', function () {
  watch('./css/less/*.less', function (files) {
    gulp.start('compile-less');
  });
});

gulp.task('compile-less', function () {
  gulp.src('./css/main.less')
    .pipe(less({ relativeUrls: true }))
    .pipe(gulp.dest('./css'));
});
