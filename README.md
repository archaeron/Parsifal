Parsifal
========

Parsifal is a parser combinator library written in PHP.


Example
-------

```php

	// parses ',' + a natural number and returns [",", number]
	$comma_natural = seq(symbol(","), natural();
	
	// returns the natural number parsed as an integer
	$successive_element = map($comma_natural,
		function($elements)
		{
			return $elements[1];
		}
	);

	// parses an arbitrary number of '$successive_element's
	$successive_elements = many($successive_element);
	
	$this->assertEquals(new Tuple([5, 6], ''), parse($successive_elements, ',5,6')->get());

	// the elements in the list are made of a natural number and of '$successive_elements'
	$list_elements = seq(natural(), $successive_elements);
	
	$this->assertEquals(new Tuple([4, 5, 6], ''), parse($list_elements, '4,5,6')->get());
	$this->assertEquals(new Tuple([4, 5, 6], 'sdfkjghsdkj'), parse($list_elements, '4,5,6sdfkjghsdkj')->get());

	// returns a function, that accepty a parser and then returns a new parser
	// between_left_right :: (Parser, Parser) -> Parser -> Parser
	$betweenBrackets = between_left_right(symbol("["), symbol("]"));
	
	// a list should have brackets around it
	$list = $betweenBrackets($list_elements);

	$this->assertEquals(new Tuple([4, 5, 6], ''), parse($list, '[4,5,6]')->get());
	$this->assertEquals(new Tuple([4, 5, 6], ''), parse($list, '[4, 5, 6]')->get());
	$this->assertEquals(new Tuple([4, 5, 6], ''), parse($list, '[ 4 , 5 , 6 ]')->get());

	$this->assertTrue(parse($list, '[ 4 , 5 , ads ]')->isEmpty());
gh
```