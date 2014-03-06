<?php

include_once 'src/parsifal.php';
use PhpOption\Option as Option;
use PhpOption\Some as Some;
use PhpOption\None as None;
use traitorous\Tuple2 as Tuple;

class ParsifalTest extends PHPUnit_Framework_TestCase
{

	/**
	* @test
	* @covers ::success, ::parse
	*/
	public function success()
	{
		$success1 = parse(success(1), "abc");
		$this->assertFalse($success1->isEmpty());
		$this->assertEquals(new Tuple(1, 'abc'), $success1->get());
	}

	/**
	* @test
	* @covers ::failure, ::parse
	*/
	public function failure()
	{
		$this->assertEquals(None::create(), parse(failure(), "abc"));
	}

	/**
	* @test
	* @covers ::item, ::parse
	*/
	public function item_parser()
	{
		$item1 = parse(item(), 'abc');
		$this->assertFalse($item1->isEmpty());
		$this->assertEquals(new Tuple('a', 'bc'), $item1->get());
		$this->assertEquals(None::create(), parse(item(), ''));
	}

	/**
	* @test
	* @covers ::flatMap, ::item, ::parse
	*/
	public function flatMap()
	{
		$flatMap1 = parse(flatMap(item(), 'success'), 'abcd');
		$this->assertEquals(new Tuple('a', 'bcd'), $flatMap1->get());

		$flatMap2 = parse(flatMap(item(), 'item'), 'abcd');
		$this->assertEquals(new Tuple('b', 'cd'), $flatMap2->get());

		$flatMap3 = parse(flatMap(failure(), 'success'), 'abcd');
		$this->assertTrue($flatMap3->isEmpty());
	}

	/**
	* @test
	* @covers ::map, ::item, ::parse
	*/
	public function map()
	{
		$map1 = parse(map(item(), function($v)
			{
				return $v.'z';
			}
		), 'abcd');
		$this->assertEquals(new Tuple('az', 'bcd'), $map1->get());

		$map2 = parse(map(item(), 'strtoupper'), 'abcd');
		$this->assertEquals(new Tuple('A', 'bcd'), $map2->get());

		$map3 = parse(map(failure(), 'success'), 'abcd');
		$this->assertTrue($map3->isEmpty());
	}

	/**
	* @test
	* @covers ::seq2
	*/
	public function seq2_parser()
	{
		$seq2_parser_item = parse(seq2(item(), item()), 'abcdef');
		$this->assertEquals(new Tuple(['a', 'b'], 'cdef'), $seq2_parser_item->get());

		$seq2_parser_failure = parse(seq2(failure(), success('d')), 'abc');
		$this->assertTrue($seq2_parser_failure->isEmpty());

		$seq2_str_parser_item = parse(seq2_str(item(), item()), 'abcdef');
		$this->assertEquals(new Tuple('ab', 'cdef'), $seq2_str_parser_item->get());

		$seq2_str_parser_failure = parse(seq2_str(failure(), success('d')), 'abc');
		$this->assertTrue($seq2_parser_failure->isEmpty());
	}

	/**
	* @test
	* @covers ::seq, ::parse
	*/
	public function seq_parser()
	{
		$seq_parser_item = parse(seq(item(), item(), item()), 'abcdef');
		$this->assertEquals(new Tuple(['a', 'b', 'c'], 'def'), $seq_parser_item->get());

		$seq_parser_failure1 = parse(seq(failure(), success('d')), 'abc');
		$this->assertTrue($seq_parser_failure1->isEmpty());

		$seq_parser_failure2 = parse(seq(success('d'), failure()), 'abc');
		$this->assertTrue($seq_parser_failure2->isEmpty());
	}

		/**
	* @test
	* @covers ::seq_str, ::parse
	*/
	public function seq_str_parser()
	{
		$seq_str_parser_item = parse(seq_str(item(), item(), item()), 'abcdef');
		$this->assertEquals(new Tuple('abc', 'def'), $seq_str_parser_item->get());

		$seq_str_parser_failure1 = parse(seq_str(failure(), success('d')), 'abc');
		$this->assertTrue($seq_str_parser_failure1->isEmpty());

		$seq_str_parser_failure2 = parse(seq_str(success('d'), failure()), 'abc');
		$this->assertTrue($seq_str_parser_failure2->isEmpty());
	}

	/**
	* @test
	* @covers ::choice
	*/
	public function choice_parser()
	{
		$choice_parser_item = parse(choice(item(), success('d')), 'abc');
		$this->assertEquals(new Tuple('a', 'bc'), $choice_parser_item->get());

		$choice_parser_return = parse(choice(failure(), success('d')), 'abc');
		$this->assertEquals(new Tuple('d', 'abc'), $choice_parser_return->get());

		$choice_parser_failure = parse(choice(failure(), failure()), 'abc');
		$this->assertTrue($choice_parser_failure->isEmpty());
	}

	/**
	* @test
	* @covers ::satisfies
	*/
	public function satisfies_parser()
	{
		$satisfies1 = parse(satisfies(function($inp){ return $inp === '1';}), '123');
		$this->assertEquals(new Tuple('1', '23'), $satisfies1->get());
		$satisfies2 = parse(satisfies(function($inp){ return $inp === '2';}), '123');
		$this->assertTrue($satisfies2->isEmpty());
		$satisfies3 = parse(satisfies(function($inp){ return $inp === '2';}), '');
		$this->assertTrue($satisfies3->isEmpty());
	}

	/**
	* @test
	* @covers ::digit
	*/
	public function digit_parser()
	{
		$digit = digit();

		$digit1 = parse($digit, '123');
		$this->assertEquals(new Tuple('1', '23'), $digit1->get());
		$digit2 = parse($digit, 'abc');
		$this->assertTrue($digit2->isEmpty());
		$digit3 = parse($digit, '');
		$this->assertTrue($digit3->isEmpty());
	}

	/**
	* @test
	* @covers ::lower
	*/
	public function lower_parser()
	{
	    $lower = lower();
	    $this->assertEquals(new Tuple('a', 'bc'), parse($lower, 'abc')->get());
	    $this->assertTrue($lower('123')->isEmpty());
	    $this->assertTrue($lower('Abc')->isEmpty());
	}

	/**
	* @test
	* @covers ::upper
	*/
	public function upper_parser()
	{
	    $upper = upper();
	    $this->assertEquals(new Tuple('A', 'bc'), parse($upper, 'Abc')->get());
	    $this->assertTrue($upper('123')->isEmpty());
	    $this->assertTrue($upper('abc')->isEmpty());
	}

	/**
	* @test
	* @covers ::letter
	*/
	public function letter_parser()
	{
	    $letter = letter();
	    $this->assertEquals(new Tuple('A', 'bc'), parse($letter, 'Abc')->get());
	    $this->assertEquals(new Tuple('a', 'bc'), parse($letter, 'abc')->get());
	    $this->assertTrue($letter('123')->isEmpty());
	    $this->assertTrue($letter('1abc')->isEmpty());
	    $this->assertTrue($letter('%1abc')->isEmpty());
	}

	/**
	* @test
	* @covers ::alphanum
	*/
	public function alphanum_parser()
	{
	    $alphanum = alphanum();
	    $this->assertEquals(new Tuple('A', 'bc'), parse($alphanum, 'Abc')->get());
	    $this->assertEquals(new Tuple('a', 'bc'), parse($alphanum, 'abc')->get());
	    $this->assertEquals(new Tuple('1', '23'), $alphanum('123')->get());
	    $this->assertEquals(new Tuple('1', 'abc'), $alphanum('1abc')->get());
	    $this->assertTrue($alphanum('%1abc')->isEmpty());
	}

	/**
	* @test
	* @covers ::char
	*/
	public function char_parser()
	{
	    $this->assertEquals(new Tuple('a', 'bc'), parse(char('a'), 'abc')->get());
	    $this->assertTrue(parse(char('b'), '123')->isEmpty());
	    $this->assertTrue(parse(char('1'), 'Abc')->isEmpty());
	}

	/**
	* @test
	* @covers ::str
	*/
	public function str_parser()
	{
		$this->assertEquals(new Tuple('abc', 'def'), parse(str('abc'), 'abcdef')->get());
		$this->assertEquals(new Tuple('', 'Abc'), parse(str(''), 'Abc')->get());
		$this->assertTrue(parse(str('abcd'), '123')->isEmpty());
		$this->assertTrue(parse(str('1234'), '123')->isEmpty());
		$this->assertTrue(parse(str('124'), '123456')->isEmpty());
	}

	/**
	* @test
	* @covers ::many
	*/
	public function many_parser()
	{
	    $this->assertEquals(new Tuple('123', 'abc'), parse(many(digit()), '123abc')->get());
	    $this->assertEquals(new Tuple('123abc', '%*'), parse(many(alphanum()), '123abc%*')->get());
	    $this->assertEquals(new Tuple('', 'abcdef'), parse(many(digit()), 'abcdef')->get());
	    $this->assertEquals(new Tuple('a', ' bcd'), parse(many(alphanum()), 'a bcd')->get());
	}

	/**
	* @test
	* @covers ::many1
	*/
	public function many1_parser()
	{
	    $this->assertEquals(new Tuple('123', 'abc'), parse(many1(digit()), '123abc')->get());
	    $this->assertEquals(new Tuple('123abc', '%*'), parse(many1(alphanum()), '123abc%*')->get());
	    $this->assertTrue(parse(many1(digit()), 'abcdef')->isEmpty());
	}

	/**
	* @test
	* @covers ::space
	*/
	public function space_parser()
	{
		$this->assertEquals(new Tuple('', 'abc'), parse(space(), '    abc')->get());
		$this->assertEquals(new Tuple('', 'abc 123'), parse(space(), 'abc 123')->get());
	}

	/**
	* @test
	* @covers ::between_left_right
	*/
	public function between_left_right_parser()
	{
		$this->assertEquals(new Tuple('', 'abc'), parse(between_left_right(char('['), char(']')), '[abc]')->get());
		$this->assertEquals(new Tuple('', 'abc 123'), parse(between_left_right(), 'abc 123')->get());
	}

	// /**
	// * @test
	// * @covers ::ident, ::identifier
	// */
	// public function identifier_parser()
	// {
	// 	$this->assertEquals(new Tuple("abc"), " def")), parse(ident(), "abc def")->get());
	// 	$this->assertEquals(new Tuple("a"), " bcdef")), parse(ident(), "a bcdef")->get());
	// 	$this->assertTrue(parse(ident(), " abc def")->isEmpty());
	// 	$this->assertEquals(new Tuple("abc"), "")), parse(identifier(), "    abc     ")->get());
	// 	$this->assertEquals(new Tuple("abc"), "123")), parse(identifier(), " abc 123")->get());
	// 	$this->assertTrue(parse(identifier(), " {} abc 123")->isEmpty());
	// }

	/**
	* @test
	* @covers ::nat, ::natural
	*/
	public function natural_parser()
	{
		// $this->assertEquals(new Tuple(123, " abc")), parse(nat(), "123 abc")->get());
		// $this->assertTrue(parse(nat(), "abc 123"))->isEmpty());
		// $this->assertEquals(new Tuple(123, "")), parse(natural(), "    123     ")->get());
		// $this->assertEquals(new Tuple(123, "123")), parse(natural(), " 123 123")->get());
		// $this->assertEquals(new Tuple(123, "123 ")), parse(natural(), " 123 123 ")->get());
		// $this->assertTrue(parse(natural(), "    abc     ")->isEmpty());
	}

	// /**
	// * @test
	// * @covers ::symbol
	// */
	// public function symbol_parser()
	// {
	// 	$this->assertEquals(new Tuple("for"), "abc")), parse(symbol("for"), "for abc")->get());
	// 	$this->assertEquals(new Tuple("for"), "abc")), parse(symbol("for"), " for abc")->get());
	// 	$this->assertTrue(parse(symbol("for"), "abc 123")->isEmpty());
	// }

	// /**
	// * @test
	// * @covers ::symbol
	// */
	// public function list_parser()
	// {
	// 	$successive_elements = many(
	// 		flatMap(symbol(","),
	// 			function()
	// 			{

	// 			}
	// 		)
	// 	);
	// 	$list_elements = seq(natural(), many(seq(symbol(","), natural())));
	// 	$evaluated_elements = flatMap($list_elements, function($elems)
	// 		{
	// 			var_dump($elems);
	// 			return success($elems);
	// 		}
	// 	);
	// 	$betweenBrackets = between_left_right(symbol("["), symbol("]"));
	// 	$list = $betweenBrackets($evaluated_elements);

	// 	$parsed = parse($list, "[1,2,3]");
	// 	$ast = $parsed[0]->_1();

	// 	$this->assertEquals([1, [2], 3], $ast);
	// 	$this->assertEquals(new Tuple([1, 2, 3], ""), parse($list, "[1, 2, 3]"));
	// 	$this->assertEquals([], parse(symbol("for"), "[1,2,]"));
	// }

}
