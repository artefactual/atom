// uppy-custom.js
// requires:
//   npm install browserify
//   npm install css-extract
//   npm install sheetify
// use: 
//   mkdir uppy; cd uppy
//   npm install @uppy/core @uppy/xhr-upload @uppy/dashboard

//   generate minified js and css:
//     browserify uppy-custom.js -t [ sheetify ] -p [ css-extract -o uppy-bundle.css ] uppy-custom.js -o uppy-bundle.js
window.Uppy = {}

const css = require('sheetify')
css('@uppy/core/dist/style.css')
css('@uppy/dashboard/dist/style.css')

Uppy.Core = require('@uppy/core')
Uppy.XHRUpload = require('@uppy/xhr-upload')
Uppy.Dashboard = require('@uppy/dashboard')
