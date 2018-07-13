# Copyright 1999-2013 Gentoo Foundation
# Distributed under the terms of the GNU General Public License v2
#
# Author: Anders Lövgren <anders.lovgren@bmc.uu.se>
# Date:   2014-02-21

EAPI="6"

PHP_EXT_NAME="phalcon"
PHP_EXT_PECL_PKG="cphalcon"
PHP_EXT_INI="yes"
PHP_EXT_ZENDEXT="no"
PHP_EXT_ECONF_ARGS="--enable-phalcon"

DOCS="${WORKDIR}/${P}/*.txt ${WORKDIR}/${P}/*.md"

USE_PHP="php5-6 php7-0 php7-1"
KEYWORDS="amd64 ~x86"

inherit php-ext-source-r3 flag-o-matic

DESCRIPTION="Web framework delivered as a C-extension for PHP"
HOMEPAGE="http://phalconphp.com/en/"
LICENSE="BSD-2"
SLOT="0"
IUSE=""
SRC_URI="https://github.com/phalcon/cphalcon/archive/v${PV}.zip -> ${P}.zip"

S="${WORKDIR}/${PN}-${PV}"
PHP_EXT_S=${S}/ext

src_prepare() {
    default 
    
    case "$ARCH" in
      amd64)
        PHALCON_ARCH=64bits
	;;
      x86)
        PHALCON_ARCH=32bits
	;;
    esac
    
    for slot in $(php_get_slots); do
      PHALCON_PHP_VERSION="${slot:0:4}"
      mkdir -p ${WORKDIR}/${slot}
      cp -a ${S}/build/${PHALCON_PHP_VERSION}/${PHALCON_ARCH}/* ${WORKDIR}/${slot}
      # Create configure script in all slots:
      ( cd ${WORKDIR}/${slot} && /usr/lib64/${slot}/bin/phpize && aclocal && libtoolize --force && autoheader && autoconf )
    done
}

src_configure() {
  append-cppflags -DPHALCON_RELEASE
  append-flags -fvisibility=hidden
  php-ext-source-r3_src_configure
}
