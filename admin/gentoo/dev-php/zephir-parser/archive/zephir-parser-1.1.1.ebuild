# Copyright 1999-2013 Gentoo Foundation
# Distributed under the terms of the GNU General Public License v2
#
# Author: Anders Lövgren <andlov@nowise.se>
# Date:   2018-04-04

EAPI="6"

PHP_EXT_NAME="zephir_parser"
PHP_EXT_PECL_PKG="zephir"
PHP_EXT_INI="yes"
PHP_EXT_ZENDEXT="no"
PHP_EXT_ECONF_ARGS="--enable-zephir_parser"

DOCS="${WORKDIR}/php-${P}/CREDITS ${WORKDIR}/php-${P}/LICENSE ${WORKDIR}/php-${P}/*.md ${WORKDIR}/php-${P}/ide"

USE_PHP="php5-5 php5-6 php7-0 php7-1 php7-2"
KEYWORDS="amd64 ~x86"

inherit php-ext-source-r3 flag-o-matic

DESCRIPTION="The Zephir Parser delivered as a C extension for the PHP language"
HOMEPAGE="http://phalconphp.com/en/"
LICENSE="MIT"
SLOT="0"
IUSE=""
SRC_URI="https://github.com/phalcon/php-${PN}/archive/v${PV}.tar.gz -> ${P}.tar.gz"

S="${WORKDIR}/php-${P}"
PHP_EXT_S=${S}/ext

RDEPEND=">=dev-util/re2c-0.13.6"

INSDIR="/usr/share/php/${PN}"

src_prepare() {
    default 
    
    for slot in $(php_get_slots); do
	PHALCON_PHP_VERSION="${slot:0:4}"
	RE2C="re2c"
	CC="gcc"
	
	mkdir -p ${WORKDIR}/${slot} || die
	cp -a ${S}/parser ${WORKDIR}/${slot} || die
	cp -a ${S}/*.{c,h,m4} ${WORKDIR}/${slot} || die
      
	# Create parser in slot:
	einfo "Creating lemon parser for zephir."
 	cd ${WORKDIR}/${slot}/parser || die

	${RE2C} -o scanner.c scanner.re || die
	${CC} lemon.c -o lemon 2> /dev/null || die

	./lemon -s parser.${PHALCON_PHP_VERSION}.lemon || die

	echo "#include <php.h>" > parser.c
	cat parser.${PHALCON_PHP_VERSION}.c >> parser.c
	cat base.c >> parser.c

	sed s/"\#line"/"\/\/"/g scanner.c > xx && mv -f xx scanner.c 
	sed s/"#line"/"\/\/"/g parser.c > xx && mv -f xx parser.c

	# Create configure script in all slots:
 	cd ${WORKDIR}/${slot} || die
	/usr/lib64/${slot}/bin/phpize && aclocal && libtoolize --force && autoheader && autoconf
    done
}

src_configure() {
  append-cppflags -DZEPHIR_PARSER_RELEASE
  append-flags -fvisibility=hidden
  php-ext-source-r3_src_configure
}

