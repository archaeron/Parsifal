# Parsifal

Parsifal is a parser combinator library written in PHP.


## Example


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

	$this->assertEquals(new Tuple(6, ''), parse($list_elements, ',6')->get());

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
```

## Functions

### Parser

A function that returns a function thet returns an Option Tpme with a Tuple in it. The left side of the Tuple is the parsed text, and the right side the remainder of the string.

```haskell
Parser a :: String -> Option (a, String) 
```

### parse

Parser application to a string

```haskell
parse :: (Parser, String) -> Result
```

```php
parse(success(1), 'abc'); // => Some(Tuple(1, 'abc'))
```

### success

Returns a parser, that always succeeds with a certain value and doesn't consume any part of the string.

```haskell
success :: a -> Parser a
```

```php
parse(success(1), 'Parse me'); // => Some(Tuple(1, 'Parse me'))
```

### failure

Returns a parser, that always fails.

```haskell
failure :: Parser a
```

### item

Returns the first char in the string.

```haskell
item :: Parser Char
```

```php
parse(item(), 'abc'); // => Some(Tuple('a', 'bc'))
```

### flatMap

```haskell
flatMap :: (Parser a, (a -> Parser b) -> Parser b
```

```php
```

### map

### seq2

```haskell
seq2 :: (Parser a, Parser b) -> Parser [a, b]
```

### seq

### seq_str

### choice

```haskell
choice :: 
```

### satisfies

Checks if the next char in the text satisfies a certain predicate.

```haskell
satisfies :: (Char -> Bool) -> Parser char
```

```php
parse(satisfies(function($inp){ return $inp === '1';}), '123'); // => Some(Tuple('1', '23'))
```

### digit

### lower

### upper

### letter

### alphanum

### char

### str

### many

### many_str

### many1

### ident

### nat

### space

### between_left_right

### between

### token

### identifier

### natural

### symbol

### ignore


