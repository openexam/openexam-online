# Copyright 1999-2013 Gentoo Foundation
# Distributed under the terms of the GNU General Public License v2
#
# Author: Anders Lövgren <anders.lovgren@bmc.uu.se>
# Date:   2014-02-21

EAPI="5"

inherit eutils php-lib-r1

DESCRIPTION="Zephir is a compiled high level language aimed to the creation of C-extensions for PHP"
HOMEPAGE="http://phalconphp.com/en/"
SRC_URI="https://github.com/phalcon/zephir/archive/master.zip -> ${PN}-master.zip"

LICENSE="MIT"
SLOT="0"
KEYWORDS="~amd64 ~x86"
IUSE=""

DEPEND="dev-libs/json-c
        dev-util/re2c"

S="${WORKDIR}/zephir-master"
INSDIR="/usr/share/php/${PN}"

src_prepare() {
    epatch ${FILESDIR}/zephir-make.diff
}

src_compile() {
    emake -C parser || die
}

src_install() {
    dodoc LICENSE *.md || die
    dobin ${FILESDIR}/zephir || die
    dodir ${INSDIR} || die
    cp -a ${S}/* ${D}${INSDIR} || die
}
