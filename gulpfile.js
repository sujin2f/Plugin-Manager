// Grab our gulp packages
var gulp  = require('gulp'),
    gutil = require('gulp-util'),
    sass = require('gulp-sass'),
    less = require('gulp-less'),
    cssnano = require('gulp-cssnano'),
    autoprefixer = require('gulp-autoprefixer'),
    sourcemaps = require('gulp-sourcemaps'),
    jshint = require('gulp-jshint'),
    stylish = require('jshint-stylish'),
    uglify = require('gulp-uglify'),
    concat = require('gulp-concat'),
    rename = require('gulp-rename'),
    plumber = require('gulp-plumber'),
    bower = require('gulp-bower'),
    babel = require('gulp-babel');

gulp.task('css', function() {
    return gulp.src('./assets/css/**/*.css')
        .pipe(plumber(function(error) {
            gutil.log(gutil.colors.red(error.message));
            this.emit('end');
        }))
        .pipe(sourcemaps.init()) // Start Sourcemaps
        .pipe(autoprefixer({
            browsers: ['last 2 versions'],
            cascade: false
        }))
        .pipe(gulp.dest('./assets/dist/css/'))
        .pipe(rename({suffix: '.min'}))
        .pipe(cssnano())
        .pipe(sourcemaps.write('.')) // Creates sourcemaps for minified styles
        .pipe(gulp.dest('./assets/dist/css/'))
});

// Compile Sass, Autoprefix and minify
gulp.task('scss', function() {
    return gulp.src('./assets/scss/**/*.scss')
        .pipe(plumber(function(error) {
            gutil.log(gutil.colors.red(error.message));
            this.emit('end');
        }))
        .pipe(sourcemaps.init()) // Start Sourcemaps
        .pipe(sass())
        .pipe(autoprefixer({
            browsers: ['last 2 versions'],
            cascade: false
        }))
        .pipe(gulp.dest('./assets/dist/css/'))
        .pipe(rename({suffix: '.min'}))
        .pipe(cssnano())
        .pipe(sourcemaps.write('.')) // Creates sourcemaps for minified styles
        .pipe(gulp.dest('./assets/dist/css/'))
});

gulp.task('less', function() {
    return gulp.src('./assets/less/**/*.less')
        .pipe(plumber(function(error) {
            gutil.log(gutil.colors.red(error.message));
            this.emit('end');
        }))
        .pipe(sourcemaps.init()) // Start Sourcemaps
        .pipe(less())
        .pipe(autoprefixer({
            browsers: ['last 2 versions'],
            cascade: false
        }))
        .pipe(gulp.dest('./assets/dist/css/'))
        .pipe(rename({suffix: '.min'}))
        .pipe(cssnano())
        .pipe(sourcemaps.write('.')) // Creates sourcemaps for minified styles
        .pipe(gulp.dest('./assets/dist/css/'))
});

// JSHint, concat, and minify JavaScript
gulp.task('scripts', function() {
  return gulp.src([
           // Grab your custom scripts
  		  './assets/scripts/**/*.js'
  ])
    .pipe(plumber())
    .pipe(sourcemaps.init())
    .pipe(jshint())
    .pipe(jshint.reporter('jshint-stylish'))
//     .pipe(concat('scripts.js'))
    .pipe(gulp.dest('./assets/dist/scripts'))
    .pipe(rename({suffix: '.min'}))
    .pipe(uglify())
    .pipe(sourcemaps.write('.')) // Creates sourcemap for minified JS
    .pipe(gulp.dest('./assets/dist/scripts'))
});

// Watch files for changes (without Browser-Sync)
gulp.task('watch', function() {
  // Watch .scss files
  gulp.watch('./assets/css/**/*.css', ['css']);

  // Watch .scss files
  gulp.watch('./assets/scss/**/*.scss', ['scss']);

  // Watch .less files
  gulp.watch('./assets/less/**/*.less', ['less']);

  // Watch site-js files
  gulp.watch('./assets/scripts/**/*.js', ['scripts']);
});

// Run styles, site-js and foundation-js
gulp.task('default', function() {
  gulp.start('css', 'scss', 'less', 'scripts');
});
