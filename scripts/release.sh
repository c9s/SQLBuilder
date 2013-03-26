#!/bin/bash
VERSION=$(cat package.ini | grep "^version" | perl -pe 's/version\s*=\s*//i;')

onion build
git commit -a -m "Release commit for $VERSION"

echo "Tagging..."
git tag $VERSION -f -m "Release $VERSION"
