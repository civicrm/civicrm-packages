#!/usr/bin/env bash

# Run selenium server with custom browser profile.
echo; echo "Runnning selenium-server-2.21.0 "; echo;
java -jar selenium-server-2.21/selenium-server-standalone-2.21.0.jar -firefoxProfileTemplate BrowserProfiles/firefox/
