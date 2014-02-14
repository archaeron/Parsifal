<?php

include 'src/parsifal.php';

class ParsifalTest extends PHPUnit_Framework_TestCase
{

	/**
	* @test
	* @covers ::success, ::parse
	*/
	public function success()
	{
		$this->assertEquals([new traitorous\Tuple2(1, "abc")], parse(success(1), "abc"));
	}

	/**
	* @test
	* @covers ::failure, ::parse
	*/
	public function failure()
	{
		$this->assertEquals([], parse(failure(), "abc"));
	}

	/**
	* @test
	* @covers ::item, ::parse
	*/
	public function item_parser()
	{
		$this->assertEquals([new traitorous\Tuple2("a", "bc")], parse(item(), "abc"));
		$this->assertEquals([], parse(item(), ""));
	}

	/**
	* @test
	* @covers ::bind, ::item, ::parse
	*/
	public function bind()
	{
	    $bind1 = bind(item(), 'success');
	    $this->assertEquals([new traitorous\Tuple2("a", "bcd")], parse($bind1, "abcd"));

	    $bind2 = bind(item(), 'item');
	    $this->assertEquals([new traitorous\Tuple2("b", "cd")], parse($bind2, "abcd"));

	    $bind3 = bind(failure(), 'success');
	    $this->assertEquals([], parse($bind3, "abcd"));
	}

	/**
	* @test
	* @covers ::seq2
	*/
	public function seq2_parser()
	{
	    $seq2_parser_item = seq2(item(), item());
	    $this->assertEquals([new traitorous\Tuple2(["a", "b"], "cdef")], parse($seq2_parser_item, "abcdef"));

	    $seq_parser_failure = seq2(failure(), success("d"));
	    $this->assertEquals([], parse($seq_parser_failure, "abc"));
	}

	/**
	* @test
	* @covers ::seq, ::parse
	*/
	public function seq_parser()
	{
		$seq_parser_item = seq(item(), item(), item());
		$this->assertEquals([new traitorous\Tuple2(["a", "b", "c"], "def")], parse($seq_parser_item, "abcdef"));

		$seq_parser_failure1 = seq(failure(), success("d"));
		$this->assertEquals([], parse($seq_parser_failure1, "abc"));

		$seq_parser_failure2 = seq(success("d"), failure());
		$this->assertEquals([], parse($seq_parser_failure2, "abc"));
	}

	/**
	* @test
	* @covers ::choice
	*/
	public function choice_parser()
	{
		$choice_parser_item = choice(item(), success("d"));
		$this->assertEquals([new traitorous\Tuple2("a", "bc")], parse($choice_parser_item, "abc"));

		$choice_parser_return = choice(failure(), success("d"));
		$this->assertEquals([new traitorous\Tuple2("d", "abc")], parse($choice_parser_return, "abc"));

		$choice_parser_failure = choice(failure(), failure());
		$this->assertEquals([], parse($choice_parser_failure, "abc"));
	}

	/**
	* @test
	* @covers ::satisfies
	*/
	public function satisfies_parser()
	{
	    $this->assertEquals([new traitorous\Tuple2("1", "23")], parse(satisfies(function($inp){ return $inp === "1";}), "123"));
	    $this->assertEquals([], parse(satisfies(function($inp){ return $inp === "2";}), "123"));
	    $this->assertEquals([], parse(satisfies(function($inp){ return $inp === "2";}), ""));
	}

	/**
	* @test
	* @covers ::digit
	*/
	public function digit_parser()
	{
	    $digit = digit();
	    $this->assertEquals([new traitorous\Tuple2("1", "23")], parse($digit, "123"));
	    $this->assertEquals([], $digit("abc"));
	    $this->assertEquals([], $digit(""));
	}

	/**
	* @test
	* @covers ::lower
	*/
	public function lower_parser()
	{
	    $lower = lower();
	    $this->assertEquals([new traitorous\Tuple2("a", "bc")], parse($lower, "abc"));
	    $this->assertEquals([], $lower("123"));
	    $this->assertEquals([], $lower("Abc"));
	}

	/**
	* @test
	* @covers ::upper
	*/
	public function upper_parser()
	{
	    $upper = upper();
	    $this->assertEquals([new traitorous\Tuple2("A", "bc")], parse($upper, "Abc"));
	    $this->assertEquals([], $upper("123"));
	    $this->assertEquals([], $upper("abc"));
	}

	/**
	* @test
	* @covers ::letter
	*/
	public function letter_parser()
	{
	    $letter = letter();
	    $this->assertEquals([new traitorous\Tuple2("A", "bc")], parse($letter, "Abc"));
	    $this->assertEquals([new traitorous\Tuple2("a", "bc")], parse($letter, "abc"));
	    $this->assertEquals([], $letter("123"));
	    $this->assertEquals([], $letter("1abc"));
	    $this->assertEquals([], $letter("%1abc"));
	}

	/**
	* @test
	* @covers ::alphanum
	*/
	public function alphanum_parser()
	{
	    $alphanum = alphanum();
	    $this->assertEquals([new traitorous\Tuple2("A", "bc")], parse($alphanum, "Abc"));
	    $this->assertEquals([new traitorous\Tuple2("a", "bc")], parse($alphanum, "abc"));
	    $this->assertEquals([new traitorous\Tuple2("1", "23")], $alphanum("123"));
	    $this->assertEquals([new traitorous\Tuple2("1", "abc")], $alphanum("1abc"));
	    $this->assertEquals([], $alphanum("%1abc"));
	}

	/**
	* @test
	* @covers ::char
	*/
	public function char_parser()
	{
	    $this->assertEquals([new traitorous\Tuple2("a", "bc")], parse(char("a"), "abc"));
	    $this->assertEquals([], parse(char("b"), "123"));
	    $this->assertEquals([], parse(char("1"), "Abc"));
	}

	/**
	* @test
	* @covers ::str
	*/
	public function str_parser()
	{
		$this->assertEquals([new traitorous\Tuple2("abc", "def")], parse(str("abc"), "abcdef"));
		$this->assertEquals([new traitorous\Tuple2("", "Abc")], parse(str(""), "Abc"));
		$this->assertEquals([], parse(str("abcd"), "123"));
		$this->assertEquals([], parse(str("1234"), "123"));
		$this->assertEquals([], parse(str("124"), "123456"));
	}

	/**
	* @test
	* @covers ::many
	*/
	public function many_parser()
	{
	    $this->assertEquals([new traitorous\Tuple2("123", "abc")], parse(many(digit()), "123abc"));
	    $this->assertEquals([new traitorous\Tuple2("123abc", "%*")], parse(many(alphanum()), "123abc%*"));
	    $this->assertEquals([new traitorous\Tuple2("", "abcdef")], parse(many(digit()), "abcdef"));
	}

	/**
	* @test
	* @covers ::many1
	*/
	public function many1_parser()
	{
	    $this->assertEquals([new traitorous\Tuple2("123", "abc")], parse(many1(digit()), "123abc"));
	    $this->assertEquals([new traitorous\Tuple2("123abc", "%*")], parse(many1(alphanum()), "123abc%*"));
	    $this->assertEquals([], parse(many1(digit()), "abcdef"));
	}

	/**
	* @test
	* @covers ::space
	*/
	public function space_parser()
	{
		$this->assertEquals([new traitorous\Tuple2("", "abc")], parse(space(), "    abc"));
		$this->assertEquals([new traitorous\Tuple2("", "abc 123")], parse(space(), "abc 123"));
	}

	/**
	* @test
	* @covers ::ident, ::identifier
	*/
	public function identifier_parser()
	{
		$this->assertEquals([new traitorous\Tuple2("abc", " def")], parse(ident(), "abc def"));
		$this->assertEquals([new traitorous\Tuple2("a", " bcdef")], parse(ident(), "a bcdef"));
		$this->assertEquals([], parse(ident(), " abc def"));
		$this->assertEquals([new traitorous\Tuple2("abc", "")], parse(identifier(), "    abc     "));
		$this->assertEquals([new traitorous\Tuple2("abc", "123")], parse(identifier(), " abc 123"));
		$this->assertEquals([], parse(identifier(), " {} abc 123"));
	}

	/**
	* @test
	* @covers ::nat, ::natural
	*/
	public function natural_parser()
	{
		$this->assertEquals([new traitorous\Tuple2(123, " abc")], parse(nat(), "123 abc"));
		$this->assertEquals([], parse(nat(), "abc 123"));
		$this->assertEquals([new traitorous\Tuple2(123, "")], parse(natural(), "    123     "));
		$this->assertEquals([new traitorous\Tuple2(123, "123")], parse(natural(), " 123 123"));
		$this->assertEquals([new traitorous\Tuple2(123, "123 ")], parse(natural(), " 123 123 "));
		$this->assertEquals([], parse(natural(), "    abc     "));
	}

	/**
	* @test
	* @covers ::symbol
	*/
	public function symbol_parser()
	{
		$this->assertEquals([new traitorous\Tuple2("for", "abc")], parse(symbol("for"), "for abc"));
		$this->assertEquals([new traitorous\Tuple2("for", "abc")], parse(symbol("for"), " for abc"));
		$this->assertEquals([], parse(symbol("for"), "abc 123"));
	}

	/**
	* @test
	* @covers ::symbol
	*/
	public function list_parser()
	{
		$list_elements = seq(natural(), many(seq(symbol(","), natural())));
		$evaluated_elements = bind($list_elements, function($elems)
			{
				var_dump($elems);
				return success($elems);
			}
		);
		$list = seq(ignore(symbol("[")), $evaluated_elements, ignore(symbol("]")));

		$parsed = parse($list, "[1,2,3]");
		$ast = $parsed[0]->_1();

		$this->assertEquals([1, [2], 3], $ast);
		$this->assertEquals([new traitorous\Tuple2([1, 2, 3], "")], parse($list, "[1, 2, 3]"));
		$this->assertEquals([], parse(symbol("for"), "[1,2,]"));
	}

	//------------- Helper functions

	/**
	* @test
	* @covers ::char_array_to_string
	*/
	public function char_array_to_string()
	{
		$this->assertEquals("Array", char_array_to_string(["A", "r", "r", "a", "y"]));
		$this->assertEquals("", char_array_to_string([]));
	}

	/**
	* @test
	* @covers ::str_head
	*/
	public function head()
	{
		$this->assertEquals("H", head("Head"));
		$this->assertEquals("1", head("12345"));
		$this->assertEquals(1, head([1, 2, 3, 4, 5]));
	}

	/**
	* @test
	* @covers ::str_tail
	*/
	public function tail()
	{
		$this->assertEquals("ead", tail("Head"));
		$this->assertEquals("2345", tail("12345"));
		$this->assertEquals([2, 3, 4, 5], tail([1, 2, 3, 4, 5]));
	}

	/**
	* @test
	* @covers ::str_prepend
	*/
	public function prepend()
	{
		$this->assertEquals("Head", prepend("H", "ead"));
		$this->assertEquals("12345", prepend("1", "2345"));
		$this->assertEquals([1, 2, 3, 4, 5], prepend(1, [2, 3, 4, 5]));
	}

}
