# Copyright 1999-2013 Gentoo Foundation
# Distributed under the terms of the GNU General Public License v2
#
# Author: Anders Lövgren <anders.lovgren@bmc.uu.se>
# Date:   2014-02-21

EAPI="5"

inherit eutils php-lib-r1

DESCRIPTION="Zephir is a compiled high level language aimed to the creation of C-extensions for PHP"
HOMEPAGE="http://phalconphp.com/en/"
SRC_URI="https://github.com/phalcon/zephir/archive/${PV}.tar.gz -> ${P}.tar.gz"

LICENSE="MIT"
SLOT="0"
KEYWORDS="amd64 x86"
IUSE="+extension -kernels -runtime -templates -test"

DEPEND="extension? (
    dev-libs/json-c 
    dev-util/re2c
)"

S="${WORKDIR}/zephir-${PV}"
INSDIR="/usr/share/php/${PN}"

src_prepare() {
    if use extension ; then
        mkdir -p ${S}/parser/build
	cp -a ${FILESDIR}/shtool ${S}/parser/build
	cp -a ${FILESDIR}/{acinclude.m4,config.nice,Makefile.global,configure.in} ${S}/parser
    fi
}

src_configure() {
    if use extension ; then
        ( 
	  cd ${S}/parser
	  autoreconf --install --force || die
	  libtoolize --install --copy || die
	  econf || die 
	)
    fi
}

src_compile() {
    if use extension ; then
        ( cd ${S}/parser/parser && ./build_linux.sh || die )
	emake -C parser || die
    fi
}

src_install() {
    dodir ${INSDIR} || die
    dobin ${FILESDIR}/zephir || die
    dodoc LICENSE *.md || die

    if use extension ; then
        emake -C parser INSTALL_ROOT="${D}" install || die
    fi

    cp -a ${S}/*.php ${S}/*.json ${D}${INSDIR} || die
    cp -a ${S}/{Library,prototypes} ${D}${INSDIR} || die
    
    if use kernels ; then
        cp -a ${S}/kernels ${D}${INSDIR} || die
    fi
    if use runtime ; then
        cp -a ${S}/runtime ${D}${INSDIR} || die
    fi
    if use templates ; then
        cp -a ${S}/templates ${D}${INSDIR} || die
    fi
    if use test ; then
        cp -a ${S}/{test,unit-tests} ${D}${INSDIR} || die
    fi    
}
