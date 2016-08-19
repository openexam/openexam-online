#!/bin/sh
#
# Fix permission on database and configure files.
#
# Author: Anders Lövgren
# Date:   2010-02-22

confdir=conf
mediadir=source/media

chmod 640 $confdir/config.inc $confdir/database.conf $confdir/catalog.def
setfacl -m u:apache:r $confdir/config.inc $confdir/database.conf $confdir/catalog.def
setfacl -m u:sysrep:r $confdir/config.inc $confdir/database.conf $confdir/catalog.def

if [ -d $mediadir/files ]; then
  # Must be writable when using automatic detection of file type.
  setfacl -m u:apache:rwx $mediadir/files
  for d in audio image video resource; do
    if [ -d $mediadir/files/$d ]; then
      setfacl -m u:apache:rwx $mediadir/files/$d
    else
      echo "$mediadir/files/$d is not a directory"
    fi
  done
fi
