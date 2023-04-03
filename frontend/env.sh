#!/bin/sh

# This script read all envs for frontend app and write it into JS file

ENV_JS="$1/env.js"

# Recreate config file
rm -rf ${ENV_JS}
touch ${ENV_JS}

# Add assignment
echo "window.env = {" >> ${ENV_JS}

# Each line represents key=value pairs
# Only "FRONTEND_APP_" prefix environments reading for security reasons
printenv | grep '^FRONTEND_APP_' | while read line
do
  # Split env variables by character `=`
  if printf '%s\n' "$line" | grep -q -e '='; then
    varname=$(printf '%s\n' "$line" | sed -e 's/=.*//')
    varvalue=$(printf '%s\n' "$line" | sed -e 's/^[^=]*=//')
  fi

  # Append configuration property to JS file
  echo "  $varname: \"$varvalue\"," >> ${ENV_JS}
done

# Close JS block
echo "}" >> ${ENV_JS}
