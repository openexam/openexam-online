EAPI="2"

inherit php-lib-r1 depend.php

DESCRIPTION="SimpleSAMLphp is an award-winning application written in native PHP that deals with authentication."
HOMEPAGE="http://simplesamlphp.org/"
SRC_URI="http://simplesamlphp.googlecode.com/files/${P}.tar.gz"

LICENSE="LGPL-2"
KEYWORDS="~x86 ~amd64"
SLOT=0
IUSE="docs"

need_php5

DEPEND=""

src_install() {
	php-lib-r1_src_install ${PN} `cd ${PN}; find . -type f -print`
	for i in ${PN}/docs/README ; do
		dodoc-php ${i}
		rm -f ${i}
	done
	if use docs ; then
	        insinto /usr/share/doc/${CATEGORY}/${PF}
		doins -r docs/*
	fi
}
