<?php

/*
 * Copyright (C) 2017-2018 The OpenExam Project
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace OpenExam\Library\Core;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2017-09-13 at 15:17:02.
 */
class SizeTest extends \PHPUnit_Framework_TestCase
{

        /**
         * @covers OpenExam\Library\Core\Size::__constructor
         * @group core
         */
        public function test__construct()
        {
                $orig = 8589934592;

                $size = new Size($orig);
                $expect = $orig;
                $actual = $size->size;
                self::assertEquals($expect, $actual);

                $size = new Size('8G');
                $expect = $orig;
                $actual = $size->size;
                self::assertEquals($expect, $actual);

                $size = new Size('8192M');
                $expect = $orig;
                $actual = $size->size;
                self::assertEquals($expect, $actual);

                $size = new Size('8388608K');
                $expect = $orig;
                $actual = $size->size;
                self::assertEquals($expect, $actual);

                $size = new Size('8589934592B');
                $expect = $orig;
                $actual = $size->size;
                self::assertEquals($expect, $actual);

                $size = new Size(array('size' => 8192, 'suffix' => 'M'));
                $expect = $orig;
                $actual = $size->size;
                self::assertEquals($expect, $actual);
        }

        /**
         * @covers OpenExam\Library\Core\Size::__get
         * @group core
         */
        public function test__get()
        {
                $orig = 8589934592;

                $size = new Size($orig);
                $expect = $orig;
                $actual = $size->size;
                self::assertEquals($expect, $actual);
                $expect = 'B';
                $actual = $size->suffix;
                self::assertEquals($expect, $actual);

                $orig = '8G';

                $size = new Size($orig);
                $expect = 8589934592;
                $actual = $size->size;
                self::assertEquals($expect, $actual);
                $expect = 'G';
                $actual = $size->suffix;
                self::assertEquals($expect, $actual);
        }

        /**
         * @covers OpenExam\Library\Core\Size::__toString
         * @group core
         */
        public function test__toString()
        {
                $orig = 8589934592;

                $size = new Size($orig);
                $expect = sprintf("%d%s", $orig, 'B');
                $actual = (string) $size;
                self::assertEquals($expect, $actual);

                $orig = '8G';
                $size = new Size($orig);
                $expect = $orig;
                $actual = (string) $size;
                self::assertEquals($expect, $actual);

                $orig = '8192M';
                $size = new Size($orig);
                $expect = $orig;
                $actual = (string) $size;
                self::assertEquals($expect, $actual);

                $size = new Size(array('size' => 8192, 'suffix' => 'M'));
                $expect = '8192M';
                $actual = (string) $size;
                self::assertEquals($expect, $actual);
        }

        /**
         * @covers OpenExam\Library\Core\Size::normalize
         * @group core
         */
        public function testNormalize()
        {
                $object = new Size('8192K');
                $expect = '8M';
                $actual = Size::normalize($object);
                self::assertEquals($expect, (string) $actual);

                $object = new Size('8192M');
                $expect = '8G';
                $actual = Size::normalize($object);
                self::assertEquals($expect, (string) $actual);

                $object = new Size('8388608K');
                $expect = '8G';
                $actual = Size::normalize($object);
                self::assertEquals($expect, (string) $actual);

                $object = new Size('8589934592B');
                $expect = '8G';
                $actual = Size::normalize($object);
                self::assertEquals($expect, (string) $actual);

                $object = new Size(8589934592);
                $expect = '8G';
                $actual = Size::normalize($object);
                self::assertEquals($expect, (string) $actual);

                $object = new Size('8388608B');
                $expect = '8M';
                $actual = Size::normalize($object);
                self::assertEquals($expect, (string) $actual);

                $object = new Size('8387584B');
                $expect = '8191K';
                $actual = Size::normalize($object);
                self::assertEquals($expect, (string) $actual);
        }

        /**
         * @covers OpenExam\Library\Core\Size::maximum
         * @group core
         */
        public function testMaximum()
        {
                $expect = '4M';
                $actual = Size::maximum(array('4M', '3M', '1M'));
                self::assertEquals($expect, (string) $actual);

                $expect = '4M';
                $actual = Size::maximum(array('4M', '4096K'));
                self::assertEquals($expect, (string) $actual);

                $expect = '4M';
                $actual = Size::maximum(array('4M', '4095K'));
                self::assertEquals($expect, (string) $actual);

                $expect = '4097K';
                $actual = Size::maximum(array('4M', '4097K'));
                self::assertEquals($expect, (string) $actual);
        }

        /**
         * @covers OpenExam\Library\Core\Size::minimum
         * @group core
         */
        public function testMinimum()
        {
                $expect = '4M';
                $actual = Size::minimum(array('4M', '6M', '7M'));
                self::assertEquals($expect, (string) $actual);

                $expect = '4M';
                $actual = Size::minimum(array('4M', '4096K'));
                self::assertEquals($expect, (string) $actual);

                $expect = '4M';
                $actual = Size::minimum(array('4M', '4097K'));
                self::assertEquals($expect, (string) $actual);

                $expect = '4095K';
                $actual = Size::minimum(array('4M', '4095K'));
                self::assertEquals($expect, (string) $actual);
        }

}
