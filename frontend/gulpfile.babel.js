'use strict';

import fs from 'fs';
import path from 'path';
import gulp from 'gulp';
import concat from 'gulp-concat';
import Dependo from 'dependo';
import babel from 'gulp-babel';
import sourcemaps from 'gulp-sourcemaps';
import less from 'gulp-less';
import cssnano from 'gulp-cssnano';
import rjs from 'gulp-requirejs-optimize';
import svgstore from 'gulp-svgstore';
import svgmin from 'gulp-svgmin';
import imagemin from 'gulp-imagemin';
import rename from 'gulp-rename';

const src = {
    scripts: 'src/js',
    views: 'src/js/views',
    less: 'src/less',
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
            .pipe(rjs((file) => {
                return Object.assign({include: path.join('views', folder, file.relative ), out: folder + '.js'}, rjsConfig);
            }))
            .pipe(concat(folder + '.js'))
            .pipe(sourcemaps.write('.'))
            .pipe(gulp.dest(dist.scripts))
    );
});

gulp.task('less', () => {
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
        .pipe(svgmin(function (file) {
            var prefix = path.basename(file.relative, path.extname(file.relative));
            return {
                plugins: [{
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
    gulp.watch(src.less + '**/*.less', ['less']);
});

gulp.task('default', ['watch', 'scripts', 'requirejs-dependencies', 'svg', 'images', 'less']);
