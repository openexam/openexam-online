EAPI=5

inherit eutils

DESCRIPTION="SimpleSAMLphp is an award-winning application written in native PHP that deals with authentication."
HOMEPAGE="http://simplesamlphp.org/"
SRC_URI="https://github.com/simplesamlphp/simplesamlphp/releases/download/v${PV}/${P}.tar.gz"

LICENSE="LGPL-2"
KEYWORDS="~x86 ~amd64"
SLOT=0
IUSE="docs"

DEPEND=""

src_install() {
	# fix directory pathes
	einfo "Fixing directory pathes"
	find -type f | while read f; do 
		if [ -n "`grep /var/simplesamlphp $f`" ]; then
			einfo "Replacing path in $f"
			sed -i "s|/var/simplesamlphp|/usr/share/php/simplesamlphp|g" $f
		fi
	done

	# install php files
        einfo "Building list of files to install"
	rm -rf config/*
	echo "Copy files from config-templates to this directory"
        insinto "/usr/share/php/${PN}"
	doins -r `ls | grep -v docs`

	# install documentation
	if use docs ; then
	        einfo "Installing documentation"
        	dodoc -r docs/*
	fi
}
