CC = gcc
CFLAGS = -O2 -g

.PHONY : all install clean

all : zephir
zephir : parser.c
	$(CC) -w parser.c scanner.c -ljson-c -o ../bin/zephir-parser

parser.c :
	re2c -o scanner.c scanner.re && ./lemon -s parser.lemon && cat base.c >> parser.c
	sed s/"\#line"/"\/\/"/g scanner.c > xx && mv -f xx scanner.c
	sed s/"#line"/"\/\/"/g parser.c > xx && mv -f xx parser.c
	
clean :
	rm -f parser/*.o
	rm -f parser/*.lo
	
