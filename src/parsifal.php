<?php

include 'vendor/autoload.php';

function parse($parser, $input)
{
	return $parser($input);
}

function success($v)
{
	return function($inp) use ($v)
	{
		return [new traitorous\Tuple2($v, $inp)];
	};
}

function failure()
{
	return function()
	{
		return [];
	};
}

function item()
{
	return function($inp)
	{
		if($inp == "")
		{
			return [];
		}
		else
		{
			return [new traitorous\Tuple2(head($inp), tail($inp))];
		}
	};
}

function bind($p, $func)
{
	return function($inp) use ($p, $func)
	{
		$result_p = parse($p, $inp);
		if($result_p === [])
		{
			return [];
		}
		else
		{
			$v = $result_p[0]->_1();
			$out = $result_p[0]->_2();
			$f = $func($v);
			return $f($out);
		}
	};
}

function seq2($a, $b)
{
	return bind($a, function($out_a) use ($b)
		{
			return bind($b, function($out_b) use ($out_a)
				{
					if(!is_array($out_a))
					{
						$out_a = [$out_a];
					}
					$out_a = prepend($out_b, $out_a);
					return success($out_a);
				}
			);
		}
	);
}

function seq()
{
	$parsers = func_get_args();

	$p = success([]);

	foreach ($parsers as $parser)
	{
		$p = seq2($p, $parser);
	}

	return $p;
}

function choice()
{
	$parsers = func_get_args();

	return function($inp) use ($parsers)
	{
		foreach($parsers as $parser)
		{
			$out = $parser($inp);
			if($out !== [])
			{
				return $out;
			}
		}
		return [];
	};
}

function satisfies($p)
{
	return bind(item(), function($x) use ($p)
		{
			if($p($x))
			{
				return success($x);
			}
			else
			{
				return failure();
			}
		}
	);
}

// Predicates

// Character Parsers
function digit()
{
	return satisfies('is_numeric');
}

function lower()
{
	return satisfies('ctype_lower');
}

function upper()
{
	return satisfies('ctype_upper');
}

function letter()
{
	return satisfies('ctype_alpha');
}

function alphanum()
{
	return satisfies('ctype_alnum');
}

function char($c)
{
	return satisfies(function($x) use ($c)
		{
			return $x === $c;
		}
	);
}

// String Parsers

function str($s)
{
	return function($inp) use ($s)
	{
		$length = strlen($s);
		if($length > strlen($inp))
		{
			return [];
		}
		else
		{
			$start = substr($inp, 0, $length);
			$end = substr($inp, $length);
			if($start === $s)
			{
				return [new traitorous\Tuple2($start, $end)];
			}
			else
			{
				return [];
			}
		}
	};
}

function many($p)
{
	return function($inp) use ($p)
	{
		$vs = [];
		$out = $inp;

		do
		{
			$result_p = $p($out);

			if($result_p === [])
			{
				break;
			}

			$v = $result_p[0]->_1();
			$out = $result_p[0]->_2();

			$vs = prepend($v, $vs);
		}
		while($out !== "");
		return [new traitorous\Tuple2(char_array_to_string($vs), $out)];
	};
}

function many1($p)
{
	return seq_to_string(seq($p, many($p)));
}

function ident()
{
	return seq_to_string(seq(lower(), many(alphanum())));
}

function nat()
{
	return bind(many1(digit()), function($nr)
		{
			return success(intval($nr));
		}
	);
}

function space()
{
	return bind(many(satisfies('ctype_space')), function()
		{
			return success("");
		}
	);
}

function token($p)
{
	return seq_to_string(seq(space(), $p, space()));
}

function identifier()
{
	return token(ident());
}

function natural()
{
	return token(nat());
}

function symbol($s)
{
	return token(str($s));
}

function ignore($p)
{
	return bind($p, function() { return success(""); } );
}

// helper functions

function flatten(array $ary)
{
	$flat = [];
	array_walk_recursive($ary, function($e) use (&$flat) { $flat[] = $e; });
	return $flat;
}

function char_array_to_string($char_array)
{
	return implode("", flatten($char_array));
}

function prepend($head, $tail)
{
	if(is_string($tail))
	{
		$tail = $head.$tail;
	}
	else
	{
		array_unshift($tail, $head);
	}
	return $tail;
}

function head($col)
{
	if(is_string($col))
	{
		return substr($col, 0, 1);
	}
	else
	{
		foreach ($col as $value)
		{
			return $value;
		}
	}
}

function tail($col)
{
	if(is_string($col))
	{
		return substr($col, 1);
	}
	else
	{
		return array_slice($col, 1);
	}
}

function seq_to_string($seq)
{
	return function($inp) use ($seq)
	{
		$result = $seq($inp);
		if($result !== [])
		{
			$v = $result[0]->_1();
			if(is_array($v))
			{
				$result = [new traitorous\Tuple2(char_array_to_string($v), $result[0]->_2())];
			}
		}
		return $result;
	};
}
?>
