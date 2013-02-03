#!/bin/sh

yuicompressor ../js/base64.js > ../js/functions-minify.js
yuicompressor ../js/functions.js >> ../js/functions-minify.js
yuicompressor ../css/styles.css -o ../css/styles-minify.css

