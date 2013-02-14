#!/bin/sh

cd "`dirname "$0"`"

yuicompressor ../js/base64.js > ../js/functions-minify.js
yuicompressor ../js/functions.js >> ../js/functions-minify.js
yuicompressor ../css/styles.css -o ../css/styles-minify.css

