EAPI=6

inherit perl-module

DESCRIPTION="Master High Availability Manager and tools for MySQL"
HOMEPAGE="https://code.google.com/p/mysql-master-ha"
SRC_URI="https://mysql-master-ha.googlecode.com/files/${PN}-${PV}.tar.gz"

LICENSE="GPL-2"
SLOT="0"
KEYWORDS="~amd64 ~x86"

DEPEND="dev-db/mysql
        dev-perl/DBI
	dev-perl/DBD-mysql
	virtual/perl-Time-HiRes
	dev-perl/Config-Tiny
	dev-perl/Log-Dispatch
	dev-perl/Parallel-ForkManager
	dev-db/mha4mysql-node"

RDEPEND="${DEPEND}"

DOCS=( AUTHORS COPYING META.yml README )
