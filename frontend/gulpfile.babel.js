'use strict';

import fs from 'fs';
import path from 'path';
import gulp from 'gulp';
import concat from 'gulp-concat';
import Dependo from 'dependo';
import babel from 'gulp-babel';
import sourcemaps from 'gulp-sourcemaps';
/* less start */import less from 'gulp-less';/* less end */
import cssnano from 'gulp-cssnano';
import rjs from 'gulp-requirejs-optimize';
import svgstore from 'gulp-svgstore';
import svgmin from 'gulp-svgmin';
import imagemin from 'gulp-imagemin';
import rename from 'gulp-rename';
/* sass start */import sass from 'gulp-sass';
import cssGlobbing from 'gulp-css-globbing';
import notify from 'gulp-notify';/* sass end */

const src = {
    scripts: 'src/js',
    views: 'src/js/views',
    /* less start */less: 'src/less',/* less end */
    /* sass start */sass: 'src/sass',/* sass end */
    svg: 'src/svg',
    img: 'src/img'
};

const dist = {
    scripts: '../www/js',
    styles: '../www/css',
    svg: '../www/svg',
    img: '../www/img'
};

const rjsConfig = {
    baseUrl: src.scripts,
    mainConfigFile: src.scripts + '/require-config.js',
    generateSourceMaps: false,

};
/* sass start */
const sassConfig = {
    errLogToConsole: true,
    outputStyle: 'expanded'
};
/* sass end */

function getFolders(dir)
{
    return fs.readdirSync(dir)
        .filter((file) => fs.statSync(path.join(dir, file)).isDirectory());
}

gulp.task('scripts', () =>
{
    let folders = getFolders(src.views);

    return folders.map((folder) =>
        gulp.src(path.join(src.views, folder, '/**/*.js'))
            .pipe(sourcemaps.init())
            .pipe(babel({
                presets: ['es2015']
            }))
            .pipe(rjs((file) =>
            {
                return Object.assign({
                    include: path.join('views', folder, file.relative),
                    out: folder + '.js'
                }, rjsConfig);
            }))
            .pipe(concat(folder + '.js'))
            .pipe(sourcemaps.write('.'))
            .pipe(gulp.dest(dist.scripts))
    );
});
/* less start */
gulp.task('less', () =>
{
    gulp.src(path.join(src.less, '/**/*.less'))
        .pipe(sourcemaps.init())
        .pipe(less())
        .pipe(concat('main.css'))
        .pipe(gulp.dest(dist.styles))
        .pipe(cssnano(
            {
                discardComments: {
                    removeAll: true
                }
            }
        ))
        .pipe(rename('main.min.css'))
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest(dist.styles));
});
/* less end */
/* sass start */
gulp.task('sass', () =>
{
    gulp.src(path.join(src.sass, '/**/*.scss'))
        .pipe(cssGlobbing({
            extensions: ['.scss'],
            autoReplaceBlock: {
                onOff: true,
                globBlockBegin: 'cssGlobbingBegin',
                globBlockEnd: 'cssGlobbingEnd',
                globBlockContents: 'modules/*.scss'
            },
            scssImportPath: {
                leading_underscore: false,
                filename_extension: false
            }
        }))
        .pipe(sourcemaps.init())
        .pipe(sass(sassConfig).on('error', notify.onError(function(error)
        {
            return 'Problem file : ' + error.message;
        })))
        .pipe(gulp.dest(dist.styles))
        .pipe(cssnano(
            {
                discardComments: {
                    removeAll: true
                }
            }
        ))
        .pipe(rename({ extname: '.min.css' }))
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest(dist.styles));
});
/* sass end */

gulp.task('requirejs-dependencies', () =>
{
    var dependencies = new Dependo([src.scripts], {
        format: 'amd',
        findNestedDependencies: true,
        requireConfig: src.scripts + '/require-config.js'
    });

    fs.writeFile('RequireJSDependencies.json', JSON.stringify(dependencies.dependencies));
});

gulp.task('svg', function()
{
    return gulp.src(path.join(src.svg, '/*.svg'))
        .pipe(svgmin(function(file)
        {
            var prefix = path.basename(file.relative, path.extname(file.relative));
            return {
                plugins: [
                    {
                        cleanupIDs: {
                            prefix: prefix + '-',
                            minify: true
                        }
                    }]
            }
        }))
        .pipe(svgstore())
        .pipe(gulp.dest(dist.svg));
});

gulp.task('images', function()
{
    return gulp.src(path.join(src.img, '/*'))
        .pipe(imagemin())
        .pipe(gulp.dest(dist.img));
});

gulp.task('watch', () =>
{
    gulp.watch(src.scripts + '/**/*.js', ['scripts']);
    gulp.watch(src.scripts + '/**/*.js', ['requirejs-dependencies']);
    gulp.watch(src.svg + '/*.svg', ['svg']);
    gulp.watch(src.img + '/*', ['images']);
    /* less start */gulp.watch(src.less + '/**/*.less', ['less']);/* less end */
    /* sass start */gulp.watch(src.sass + '/**/*.scss', ['sass']);/* sass end */
});

gulp.task('default', ['watch', 'scripts', 'requirejs-dependencies', 'svg', 'images', /* sass start */'sass'/* sass end *//* less start */'less'/* less end */]);
