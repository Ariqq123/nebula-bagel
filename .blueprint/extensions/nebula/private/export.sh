#!/bin/bash
# prpl.wtf
# github.com/prplwtf

cd "$PTERODACTYL_DIRECTORY" || return

MINIFY() {
  local dir="$1"
  for file in "$dir"/*; do
    if [ -f "$file" ]; then
      file=$(echo "$file" | sed "s~ ~\ ~g")
      if [[ ( $file != *"node_modules"* ) && ( $file != *"/editor/"* ) && ( $file != *"/preview/"* ) ]]; then
        if [[ $file == *".js" ]]; then
          echo -e "\x1b[34;3;1m(@node-minify/uglify-js) \x1b[37m${file}\x1b[0m"

          "$PTERODACTYL_DIRECTORY/node_modules/@node-minify/cli/dist/cli.mjs" \
            --compressor uglify-js \
            --option '{
              "mangle": true,
              "webkit": true,
              "compress": true,
              "annotations": false
            }' \
            --input "$file" \
            --output "$file" \
            --silence
        fi
        if [[ $file == *".css" ]]; then
          echo -e "\x1b[34;3;1m(@node-minify/clean-css) \x1b[37m${file}\x1b[0m"

          "$PTERODACTYL_DIRECTORY/node_modules/@node-minify/cli/dist/cli.mjs" \
            --compressor clean-css \
            --input "$file" \
            --output "$file" \
            --silence
        fi
        if [[ $file == *".html" ]]; then
          echo -e "\x1b[34;3;1m(@node-minify/html-minifier) \x1b[37m${file}\x1b[0m"

          "$PTERODACTYL_DIRECTORY/node_modules/@node-minify/cli/dist/cli.mjs" \
            --compressor html-minifier \
            --input "$file" \
            --output "$file" \
            --silence
        fi
        if [[ $file == *".less" ]]; then
          echo -e "\x1b[34;3;1m(less) \x1b[37m${file}\x1b[0m"

          yarn lessc ".blueprint/tmp/${file}" ".blueprint/tmp/${file%.less}.css" 2> /dev/null
          rm "${file}"

          echo -e "\x1b[34;3;1m(@node-minify/clean-css) \x1b[37m${file%.less}.css\x1b[0m"

          "$PTERODACTYL_DIRECTORY/node_modules/@node-minify/cli/dist/cli.mjs" \
            --compressor clean-css \
            --input "${file%.less}.css" \
            --output "${file%.less}.css" \
            --silence
        fi
      fi
    elif [ -d "$file" ]; then
      MINIFY "$file"
    fi
  done
}

yarn add \
  @node-minify/cli@8.0.6 \
  @node-minify/clean-css@8.0.6 \
  @node-minify/html-minifier@8.0.6 \
  @node-minify/uglify-js@8.0.6 -g
yarn add less --dev
cd "$BLUEPRINT_EXPORT_DIRECTORY" || return

mkdir -p .precompress/libraries
cp public/libraries/*.css .precompress/libraries/
cp public/libraries/*.js .precompress/libraries/

MINIFY "."

mv .precompress precompress
touch precompress/README
echo "Uncompressed versions of Nebula assets for development purposes." > precompress/README

# shellcheck disable=SC2193
if [[ "$EXTENSION_VERSION" == *"-beta" ]]; then
  echo -e "\x1b[34;1mApplying Nebula Insiders patches.\x1b[0m"
  sed -i \
    -e 's/icon: "nebula.jpg"/icon: "nebula-insiders.jpg"/g' \
    -e 's/name: "Nebula"/name: "Nebula Insiders"/g' \
    ./conf.yml
fi

rm \
  yarn.lock \
  package.json \
  README.* \
  README \
  .gitkeep \
  .gitignore \
  export.sh \
  2> /dev/null

rm -R \
  node_modules \
  .ignore \
  2> /dev/null
