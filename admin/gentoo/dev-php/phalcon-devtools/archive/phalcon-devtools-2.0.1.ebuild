# Copyright 1999-2013 Gentoo Foundation
# Distributed under the terms of the GNU General Public License v2
#
# Author: Anders Lövgren <anders.lovgren@bmc.uu.se>
# Date:   2014-02-21

EAPI="5"

inherit eutils php-lib-r1

DESCRIPTION="Phalcon Developer Tools"
HOMEPAGE="http://phalconphp.com/en/"
SRC_URI="https://github.com/phalcon/phalcon-devtools/archive/v${PV}.zip -> ${P}.zip"

LICENSE="BSD-2"
SLOT="0"
KEYWORDS="amd64 x86"
IUSE=""

DEPEND="dev-php/cphalcon"

S="${WORKDIR}/${P}"
INSDIR="/usr/share/php/${PN}"

src_install() {
    dodoc docs/* README.md
    dodir ${INSDIR}
    cp -a ${S}/* ${D}${INSDIR} || die
    dosym ${INSDIR}/phalcon.php /usr/bin/phalcon || die
}
