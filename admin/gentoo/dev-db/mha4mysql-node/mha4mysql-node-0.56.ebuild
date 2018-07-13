EAPI=6

inherit perl-module

DESCRIPTION="Master High Availability Manager and tools for MySQL"
HOMEPAGE="https://code.google.com/p/mysql-master-ha"
SRC_URI="https://72003f4c60f5cc941cd1c7d448fc3c99e0aebaa8.googledrive.com/host/0B1lu97m8-haWeHdGWXp0YVVUSlk/${PN}-${PV}.tar.gz"

LICENSE="GPL-2"
SLOT="0"
KEYWORDS="~amd64 ~x86"

DEPEND="dev-db/mysql
        dev-perl/DBI
	dev-perl/DBD-mysql"

RDEPEND="${DEPEND}"

DOCS=( AUTHORS COPYING META.yml README )
