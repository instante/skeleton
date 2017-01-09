'use strict';

import fs from 'fs';
import path from 'path';
import gulp from 'gulp';
import Dependo from 'dependo';
import babel from 'gulp-babel';
import sourcemaps from 'gulp-sourcemaps';
 
import cssnano from 'gulp-cssnano';
import rjs from 'gulp-requirejs-optimize';
import svgstore from 'gulp-svgstore';
import svgmin from 'gulp-svgmin';
import imagemin from 'gulp-imagemin';
import rename from 'gulp-rename';
import sass from 'gulp-sass';
import cssGlobbing from 'gulp-css-globbing';
import notify from 'gulp-notify';
import gulpGrunt from 'gulp-grunt';

gulpGrunt(gulp);

const src = {
    scripts: 'src/js',
    es5: 'src/es5',
    views: 'src/es5/views',
     
    sass: 'src/sass',
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
    baseUrl: src.es5,
    mainConfigFile: src.es5 + '/require-config.js',
    generateSourceMaps: true,
    preserveLicenseComments: false
};

const sassConfig = {
    errLogToConsole: true,
    outputStyle: 'expanded'
};


function getFolders(dir)
{
    return fs.readdirSync(dir)
        .filter((file) => fs.statSync(path.join(dir, file)).isDirectory());
}

function getCommon()
{
    let commonFiles = listFiles(path.join(src.views, 'common'));
    return [
        ...commonFiles,
        'require-config',
        '../../bower_components/requirejs/require',
        'instante/container',
        'bootstrap'
    ];
}

function listFiles(dir) {
    return fs.readdirSync(dir).reduce(function(list, file) {
        var name = path.join(dir, file);
        var isDir = fs.statSync(name).isDirectory();
        return list.concat(isDir ? listFiles(name) : [name.replace(src.es5 + '/', '').replace('.js', '')]);
    }, []);
}

gulp.task('es5', (cb) =>
{
    gulp.src(path.join(src.scripts, '/**/*.js'))
        .pipe(babel({
            presets: ['es2015']
        }))
        .pipe(gulp.dest(src.es5))
        .on('end', () => cb()); // use callback to make script synchronous
});

gulp.task('scripts', ['es5'], () =>
{
    let folders = getFolders(src.views);
    let commonFiles = getCommon();
    return folders.map((folder) =>
    {
        if (folder !== 'common') {
            let files = listFiles(path.join(src.views, folder));
            files = files.concat(commonFiles);
            gulp.src(path.join(src.es5, '/require-config.js'))
                .pipe(rjs(
                    Object.assign({
                        include: files,
                        out: folder + '.min.js'
                    }, rjsConfig)
                ))
                .pipe(gulp.dest(dist.scripts));
        }
    });
});
 

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
     
    gulp.watch(src.sass + '/**/*.scss', ['sass']);
});

gulp.task('dist', ['scripts', 'requirejs-dependencies', 'svg', 'images', 'sass' ]);
gulp.task('dev', ['watch', 'requirejs-dependencies', 'svg', 'images', 'sass' ]);
gulp.task('test', ['grunt-tests-cli']);

gulp.task('default', ['dev']);
