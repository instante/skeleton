'use strict';

import fs from 'fs';
import path from 'path';
import merge from 'merge-stream';
import gulp from 'gulp';
import concat from 'gulp-concat';
import rename from 'gulp-rename';
import uglify from 'gulp-uglify';
import Dependo from 'dependo';
import babel from 'gulp-babel';
import sourcemaps from 'gulp-sourcemaps';
import less from 'gulp-less';
import cssnano from 'gulp-cssnano';

const src = {
    scripts: 'src/js',
    less: 'src/less'
};

const dist = {
    scripts: '../www/js',
    styles: '../www/css'
};

function getFolders(dir)
{
    return fs.readdirSync(dir)
        .filter((file) => fs.statSync(path.join(dir, file)).isDirectory());
}

gulp.task('scripts', () =>
{
    let folders = getFolders(src.scripts);

    let tasks = folders.map((folder) =>
        gulp.src(path.join(src.scripts, folder, '/**/*.js'))
            .pipe(sourcemaps.init())
            .pipe(babel({
                presets: ['es2015']
            }))
            .pipe(concat(folder + '.js'))
            .pipe(uglify())
            .pipe(rename(folder + '.min.js'))
            .pipe(sourcemaps.write('.'))
            .pipe(gulp.dest(dist.scripts))
    );

    // concat remaining files to main js file
    let root = gulp.src(path.join(src.scripts, '/*.js'))
        .pipe(sourcemaps.init())
        .pipe(babel({
            presets: ['es2015']
        }))
        .pipe(uglify())
        .pipe(rename('main.min.js'))
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest(dist.scripts));

    return merge(tasks, root);
});

gulp.task('less', () => {
    gulp.src(path.join(src.less, '/**/*.less'))
        .pipe(sourcemaps.init())
        .pipe(less())
        .pipe(concat('main.min.css'))
        .pipe(cssnano(
            {
                discardComments: {
                    removeAll: true
                }
            }
        ))
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

gulp.task('watch', () =>
{
    gulp.watch(src.scripts + '/**/*.js', ['scripts']);
    gulp.watch(src.scripts + '/**/*.js', ['requirejs-dependencies']);
});

gulp.task('default', ['watch', 'scripts', 'requirejs-dependencies']);
