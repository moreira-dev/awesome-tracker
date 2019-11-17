var gulp = require("gulp"),
    sass = require("gulp-sass"),
    postcss = require("gulp-postcss"),
    autoprefixer = require("autoprefixer"),
    cssnano = require("cssnano"),
    sourcemaps = require("gulp-sourcemaps");


var paths = {
    css: "css/*.css",
    inc: "inc/**/*",
    js: {
        dist: "js/dist/*",
        external: "js/external/**/*",
        js: "js/*.js"
    },
    php : "./*.php",
    license: "./LICENSE",
    readme: "./readme.txt",
    dest: "./svn/trunk",

    styles: {
        // By using styles/**/*.sass we're telling gulp to check all folders for any sass file
        src: "css/scss/*.scss",
        // Compiled files will end up in whichever folder it's found in (partials are not compiled)
        dest: "./css/"
    }
};

var base = {base: "."};


function build() {
    return gulp
        .src(paths.css, base)
        .pipe(gulp.src(paths.inc,base))
        .pipe(gulp.src(paths.js.dist,base))
        .pipe(gulp.src(paths.js.external,base))
        .pipe(gulp.src(paths.js.js,base))
        .pipe(gulp.src(paths.php,base))
        .pipe(gulp.src(paths.license,base))
        .pipe(gulp.src(paths.readme,base))
        .pipe(gulp.dest(paths.dest));
}

function style() {
    return gulp
        .src(paths.styles.src)
        // Initialize sourcemaps before compilation starts
        .pipe(sourcemaps.init())
        .pipe(sass())
        .on("error", sass.logError)
        // Use postcss with autoprefixer and compress the compiled file using cssnano
        .pipe(postcss([autoprefixer(), cssnano()]))
        // Now add/write the sourcemaps
        .pipe(sourcemaps.write())
        .pipe(gulp.dest(paths.styles.dest));
}

function stylePROD() {
    return gulp
        .src(paths.styles.src)
        .pipe(sass())
        .on("error", sass.logError)
        .pipe(postcss([autoprefixer(), cssnano()]))
        .pipe(gulp.dest(paths.styles.dest));
}


// Add browsersync initialization at the start of the watch task
function watch() {
    gulp.watch(paths.styles.src, style);
}

var runBuild = gulp.series(stylePROD, build);

// Expose the task by exporting it
// This allows you to run it from the commandline using
// $ gulp style
exports.build = runBuild;

exports.watch = watch;

exports.style = style;


/*
 * You can still use `gulp.task` to expose tasks
 */
gulp.task('build', runBuild);

/*
 * Define default task that can be called by just running `gulp` from cli
 */
gulp.task('default', runBuild);


/*
 * Specify if tasks run in series or parallel using `gulp.series` and `gulp.parallel`
 */
var parallel = gulp.parallel(style, watch);

/*
 * You can still use `gulp.task` to expose tasks
 */
gulp.task('style', parallel);

