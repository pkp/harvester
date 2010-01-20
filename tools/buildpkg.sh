#!/bin/bash

#
# buildpkg.sh
#
# Copyright (c) 2005-2010 Alec Smecher and John Willinsky
# Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
#
# Script to create a Harvester2 package for distribution.
#
# Usage: buildpkg.sh <version> [<tag>]
#
# $Id$
#

CVSROOT=:pserver:anonymous@pkp.sfu.ca:/cvs
HARVESTERMODULE=harvester2
PKPMODULE=pkp

if [ -z "$1" ]; then
	echo "Usage: $0 <version> [<tag>] [<patch_dir>]";
	exit 1;
fi

VERSION=$1
TAG=${2-HEAD}
PATCHDIR=${3-}
PREFIX=harvester
BUILD=$PREFIX-$VERSION
TMPDIR=`mktemp -d $PREFIX.XXXXXX` || exit 1

EXCLUDE="dbscripts/xml/data/locale/te_ST			\
docs/dev							\
locale/te_ST							\
plugins/harvesters/junk						\
tools/buildpkg.sh						\
tools/genTestLocale.php"

cd $TMPDIR

echo -n "Exporting $HARVESTERMODULE with tag $TAG ... "
cvs -Q -d $CVSROOT export -r $TAG -d $BUILD $HARVESTERMODULE || exit 1
echo "Done"

echo -n "Exporting $PKPMODULE with tag $TAG ... "
cvs -Q -d $CVSROOT export -r $TAG $PKPMODULE || exit 1
echo "Done"

if [ ! -d $BUILD/lib ]; then
	mkdir $BUILD/lib
fi
mv $PKPMODULE $BUILD/lib/$PKPMODULE

cd $BUILD

echo -n "Preparing package ... "
cp config.TEMPLATE.inc.php config.inc.php
mkdir cache/t_cache
mkdir plugins/postprocessors
find . -name .cvsignore -exec rm {} \;
rm -r $EXCLUDE
echo "Done"

cd ..

echo -n "Creating archive $BUILD.tar.gz ... "
tar -zcf ../$BUILD.tar.gz $BUILD
echo "Done"

if [ ! -z "$PATCHDIR" ]; then
	echo "Creating patches in $BUILD.patch ..."
	[ -e "../${BUILD}.patch" ] || mkdir "../$BUILD.patch"
	for FILE in $PATCHDIR/*; do
		OLDBUILD=$(basename $FILE)
		OLDVERSION=${OLDBUILD/$PREFIX-/}
		OLDVERSION=${OLDVERSION/.tar.gz/}
		echo -n "Creating patch against ${OLDVERSION} ... "
		tar -zxf $FILE
		diff -urN $PREFIX-$OLDVERSION $BUILD | gzip -c > ../${BUILD}.patch/$PREFIX-${OLDVERSION}_to_${VERSION}.patch.gz
		echo "Done"
	done
	echo "Done"
fi

cd ..
rm -r $TMPDIR
