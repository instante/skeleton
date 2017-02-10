## JavaScript
Gulp don't build scripts in default watch task because in development mode, source JS files are used.

```
# build scripts for production
your-project/frontend$ gulp scripts

# or build everything at once
your-project/frontend$ gulp dist

# run watchdog and build everything except of production JS on the fly:
your-project/frontend$ gulp
```

For production environment scripts are minified and combined into separated files for each view module which include all defined dependencies.

```
├─frontend
│   ├─ src
│   │   ├─ js
│   │   │   └─ views
│   │   │       ├─ admin
│   │   │       └─ front
```
Shown folder structure will be combined into two files: `admin.js` and `front.js`

It's recommended to separate JS for each view of your application.

### Tests

NOTE: npm module grunt-mocha-require-phantom:0.8.0 has a dependency
on grunt-lib-phantomjs:0.7.0 which is broken on MacOS 10.12 and maybe
others. To temporarily fix this until my pull request is merged
into g-m-r-p, copy `frontend/node_modules/grunt-lib-phantomjs` folder
into `frontend/node_modules/grunt-mocha-require-phantom/node_modules`
and replace the one here.

Usage:

```
frontend$ gulp test
```

## CSS
Instante is prepared with _sass_ support for compiling stylesheets.

Input files `frontend/src/sass/fileName.sass` will be outputted to
`www/css/fileName.min.css` - your separate files (sass modules)
placed in `frontend/src/sass/modules/` folder will be combined
into `main.min.css` automatically.

## SVG and images

### Images
Gulp optimizes images into `www/img` folder so you can simply save
images to `fronted/src/img` folder.

### SVG
SVGs are combined to one `svg.svg`. You can display svg in template
using _#name_ parameter

```
<svg>
    <use xlink:href="{$basePath}/svg/svg.svg#name"/>
</svg>
```
