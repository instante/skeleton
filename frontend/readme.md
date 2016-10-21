## JavaScript
Gulp don't build scripts in default watch task because in development mode, source JS files are used.

```
# build scripts for production
your-project/frontend$ gulp scripts
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

It's recommended separate JS for each view of your application.

##CSS
Instante provides _less_ and _sass_ support. You can choose one of preprocessors on project init.
###LESS
Input file `frontend/src/less/main.less` will be outputed to `www/css/main.min.css` - you should separate less styles into separate files and import them to `main.less`
###SASS
Input files `frontend/src/sass/fileName.sass` will be outputed to `www/css/fileName.min.css` - your separate files (sass modules) placed in `frontend/src/sass/modules/` folder will be combined into `main.min.css` automatically.
##SVG and images
### Images
Gulp optimizes images into `www/img` folder so you can simply save images to `fronted/src/img` folder.

### SVG
SVGs are combined to one `svg.svg`. You can display svg in template using _#name_ parameter

```
<svg>
    <use xlink:href="{$basePath}/svg/svg.svg#name"/>
</svg>
```
