# Copyright 1999-2013 Gentoo Foundation
# Distributed under the terms of the GNU General Public License v2
#
# Author: Anders Lövgren <anders.lovgren@bmc.uu.se>
# Date:   2014-02-21

EAPI="5"

inherit eutils

DESCRIPTION="Zephir is a compiled high level language aimed to the creation of C-extensions for PHP"
HOMEPAGE="http://phalconphp.com/en/"
SRC_URI="https://github.com/phalcon/zephir/archive/${PV}.tar.gz -> ${P}.tar.gz"

LICENSE="MIT"
SLOT="0"
KEYWORDS="amd64 x86"
IUSE="-runtime -templates -test"

S="${WORKDIR}/zephir-${PV}"
INSDIR="/usr/share/php/${PN}"

RDEPEND="dev-php/zephir-parser"

src_install() {
    dodir ${INSDIR} || die
    dobin ${FILESDIR}/zephir || die
    dodoc LICENSE *.md || die

    cp -a ${S}/*.php ${S}/*.json ${D}${INSDIR} || die
    cp -a ${S}/{Library,prototypes,kernels} ${D}${INSDIR} || die
    
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
