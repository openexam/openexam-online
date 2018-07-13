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
DOCS="docs/*"

USE_PHP="php5-3 php5-4 php5-5"

inherit php-ext-source-r2 git-2 autotools

KEYWORDS="~amd64 ~x86"

DESCRIPTION="Web framework delivered as a C-extension for PHP"
HOMEPAGE="http://phalconphp.com/en/"
LICENSE="BSD-2"
SLOT="0"
IUSE=""

EGIT_REPO_URI="git://github.com/phalcon/cphalcon.git"

# cd build && ./install-gentoo
# php-ext-pecl-r2_src_install

builddir() {
    case "$HOSTTYPE" in
      x86_64)
        echo "build/64bits"
	;;
      i386)
        echo "build/32bits"
	;;
    esac
}

BUILDDIR="${WORKDIR}/${P}/$(builddir)"

src_prepare() {
    echo "++ Prepare hook"
    cp -a $BUILDDIR/* .
    phpize || die "failed run phpize"
    eautoreconf || die "failed run autoreconf"
    aclocal --force
    autoconf		    
}

src_configure() {
    echo "++ Configure hook"
    ./configure --enable-phalcon || die "failed configure"
}

# src_compile() {
#     echo "++ Compile hook"
#     pushd $BUILDDIR
#     emake || die "failed compile"
#     popd
# }

# src_install() {
#     echo "++ Install hook"
#     pushd $BUILDDIR
#     emake install INSTALL_ROOT=$D || die "failed install"
#     popd
#     php-ext-source-r2_src_install
# }
