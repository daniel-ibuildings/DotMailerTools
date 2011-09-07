#!/bin/bash

##############################################################################
#
# buildpackage.sh
#
# Build an installable Sugar package $PACKAGE from the $SRCDIR directory.
# Written by Sander Marechal <s.marechal@jejik.com>
# Released into the Public Domain
#
##############################################################################

PACKAGE="DotMailerTools"
SRCDIR="src"
BUILDDIR="build"
STAMP=`date '+%Y%m%d%H%M%S'`
DATE=`date '+%Y%m%d%H%M%S'`

# find the local and remote revision numbers
#LOCALREV=`svn info -R $SRCDIR | sed -n 's/Revision: \([0-9]\+\)/\1/p' | sort -ur | head -n 1`
#REMOTEURL=`svn info $SRCDIR | sed -n 's/URL: \(.*\)/\1/p'`
#REMOTEREV=`svn info $REMOTEURL -R | sed -n 's/Revision: \([0-9]\+\)/\1/p' | sort -ur | head -n 1`

PACKAGEDIR=$BUILDDIR/$PACKAGE-$STAMP
VERSION=$(date +%s)
ZIPFILE=$PACKAGE-$VERSION.zip

#svn_update() {
#    read UPDATE;
#    case "$UPDATE" in
#        [yY]*|"")
#            svn update;
#            LOCALREV=`svn info $SRCDIR | sed -n 's/Revision: \([0-9]\+\)/\1/p'`
#            ZIPFILE=$PACKAGE-r$LOCALREV.zip
#            ;;
#        [nN]*)
#            ;;
#        [aAqQ]*)
#            exit 0;
#            ;;
#        *)
#            echo -n "Please enter [Y]es, [n]o or [a]bort: ";
#            svn_update
#            ;;
#    esac
#}

#keep_local_changes() {
#    read KEEPCHANGES
#    case "$KEEPCHANGES" in
#        [yY]*|"")
#            VERSION=$VERSION+$STAMP
#            ZIPFILE=$PACKAGE-$VERSION.zip
#            ;;
#        [nNaAqQ]*)
#            echo "Please commit your changes first";
#            exit 0;
#            ;;
#        *)
#            echo -n "Please enter [Y]es or [n]o: ";
#            keep_local_changes
#            ;;
#    esac
#}

#if [ "$LOCALREV" -lt "$REMOTEREV" ]; then
#    echo "Local working copy seems out of date. Working copy is at r$LOCALREV but HEAD is at r$REMOTEREV";
#    echo -n "Do you want to run 'svn update' [Y/n/a]? ";
#    svn_update
#fi

# Check for local changes
#CHANGES=`svn status $PACKAGE | grep -v "^\?" | wc -l`

#if [ "$CHANGES" -gt 0 ]; then
#    echo -n "Local working copy has uncommitted changes. Continue [Y/n]? ";
#    keep_local_changes
#fi

# Create the build directory
if [ ! -d "$BUILDDIR" ]; then
    mkdir $BUILDDIR;
fi

# Copy package to the build directory
mkdir $PACKAGEDIR;
cp -r $SRCDIR/* $PACKAGEDIR/;

# Remove all the .svn dirs
#SVNDIRS=`find $PACKAGEDIR -name ".svn"`
#for SVNDIR in "$SVNDIRS"; do
#    rm -rf $SVNDIR;
#done

# Remove all the .swp files from Vim
#SWPFILES=`find $PACKAGEDIR -name "*.swp"`
#for SWPFILE in "$SWPFILES"; do
#    rm -f $SWPFILE;
#done

# Replace @VERSION@ and @DATE@ in the manifest
sed -e "s/@VERSION@/$VERSION/g" -e "s/@DATE@/$DATE/g" $PACKAGEDIR/manifest.php > $PACKAGEDIR/manifest2.php
rm -f $PACKAGEDIR/manifest.php
mv $PACKAGEDIR/manifest2.php $PACKAGEDIR/manifest.php

# Create the zip file
if [ -f $ZIPFILE ]; then
    rm -f $ZIPFILE;
fi

cd $PACKAGEDIR
zip -qr ../$ZIPFILE .;
cd ../..;

# Clean the build directory
rm -rf $PACKAGEDIR;

# All done
echo "Succcesfully built package $BUILDDIR/$ZIPFILE";
exit