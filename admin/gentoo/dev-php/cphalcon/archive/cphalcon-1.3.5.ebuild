# Copyright 1999-2013 Gentoo Foundation
# Distributed under the terms of the GNU General Public License v2
#
# Author: Anders Lövgren <anders.lovgren@bmc.uu.se>
# Date:   2014-02-21

EAPI="5"

PHP_EXT_NAME="phalcon"
PHP_EXT_PECL_PKG="cphalcon"
PHP_EXT_INI="yes"
PHP_EXT_ZENDEXT="no"
DOCS="${WORKDIR}/${P}/docs/*.txt ${WORKDIR}/${P}/*.md"

USE_PHP="php5-3 php5-4 php5-5"
KEYWORDS="amd64 x86"

inherit php-ext-source-r2 autotools eutils

DESCRIPTION="Web framework delivered as a C-extension for PHP"
HOMEPAGE="http://phalconphp.com/en/"
LICENSE="BSD-2"
SLOT="0"
IUSE=""
SRC_URI="https://github.com/phalcon/cphalcon/archive/${PV}.tar.gz"

S=${WORKDIR}/${PN}-${PV}

builddir() {
    case "$HOSTTYPE" in
      x86_64)
        echo "build/64bits"
	;;
      i386|i486|i586|i686)
        echo "build/32bits"
	;;
    esac
}

BUILDDIR="${WORKDIR}/${S}/$(builddir)"
PHP_EXT_S="${S}/$(builddir)"

my_conf="--enable-phalcon"
