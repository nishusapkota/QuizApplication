<?php

declare(strict_types=1);

namespace PhpMyAdmin\SqlParser\Tests\Utils;

use PhpMyAdmin\SqlParser\Lexer;
use PhpMyAdmin\SqlParser\Parser;
use PhpMyAdmin\SqlParser\Tests\TestCase;
use PhpMyAdmin\SqlParser\Utils\Error;

class ErrorTest extends TestCase
{
    public function testGet(): void
    {
        $lexer = new Lexer('SELECT * FROM db..tbl }');
        $parser = new Parser($lexer->list);
        $this->assertEquals(
            [
                [
                    'Unexpected character.',
                    0,
                    '}',
                    22,
                ],
                [
                    'Unexpected dot.',
                    0,
                    '.',
                    17,
                ],
            ],
            Error::get([$lexer, $parser])
        );
    }

    public function testGetWithNullToken(): void
    {
        $lexer = new Lexer('LOCK TABLES table1 AS `t1` LOCAL');
        $parser = new Parser($lexer->list);
        $this->assertSame(
            [
                ['An alias was previously found.', 0, 'LOCAL', 27],
                ['Unexpected keyword.', 0, 'LOCAL', 27],
                ['Unexpected end of LOCK expression.', 0, '', null],
            ],
            Error::get([$lexer, $parser])
        );
    }

    public function testFormat(): void
    {
        $this->assertEquals(
            ['#1: error msg (near "token" at position 100)'],
            Error::format([['error msg', 42, 'token', 100]])
        );
        $this->assertEquals(
            [
                '#1: error msg (near "token" at position 100)',
                '#2: error msg (near "token" at position 200)',
            ],
            Error::format([['error msg', 42, 'token', 100], ['error msg', 42, 'token', 200]])
        );
    }
}
