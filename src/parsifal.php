<?php

include 'vendor/autoload.php';
use PhpOption\Option as Option;
use PhpOption\Some as Some;
use PhpOption\None as None;
use traitorous\Tuple2 as Tuple;

function parse($parser, $input)
{
	return $parser($input);
}

function success($v)
{
	return function($inp) use ($v)
	{
		return new Some(new Tuple($v, $inp));
	};
}

function failure()
{
	return function()
	{
		return None::create();
	};
}

function item()
{
	return function($inp)
	{
		if($inp === '')
		{
			return None::create();
		}
		else
		{
			return new Some(new Tuple(str_head($inp), str_tail($inp)));
		}
	};
}

function flatMap($p, $func)
{
	return function($inp) use ($p, $func)
	{
		$result_p = parse($p, $inp);

		return $result_p->flatMap(function($res) use ($func)
			{
				$value = value($res);
				$rest = rest($res);
				$f = $func($value);
				return $f($rest);
			}
		);
	};
}

function map($p, $func)
{
	return flatMap($p, function($value) use ($func)
		{
			return success($func($value));
		}
	);
}

function seq2($a, $b)
{
	return flatMap($a, function($out_a) use ($b)
		{
			return flatMap($b, function($out_b) use ($out_a)
				{
					if(! is_array($out_a)) $out_a = [$out_a];
					if(! is_array($out_b)) $out_b = [$out_b];

					return success(array_merge($out_a, $out_b));
				}
			);
		}
	);
}

function seq2_str($a, $b)
{
	return map(seq2($a, $b), function($val)
		{
			return implode('', $val);
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

function seq_str()
{
	$parsers = func_get_args();

	$p = success([]);

	foreach ($parsers as $parser)
	{
		$p = seq2_str($p, $parser);
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
			$out = parse($parser, $inp);
			if(! $out->isEmpty())
			{
				return $out;
			}
		}
		return None::create();
	};
}

function satisfies($p)
{
	return flatMap(item(), function($x) use ($p)
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
		$length = str_length($s);
		if($length > str_length($inp))
		{
			return None::create();
		}
		else
		{
			$start = substr($inp, 0, $length);
			$end = substr($inp, $length);
			if($start === $s)
			{
				return new Some(new Tuple($start, $end));
			}
			else
			{
				return None::create();
			}
		}
	};
}

function many($p)
{
	return function($inp) use ($p)
	{
		$values = [];
		$out = $inp;

		do
		{
			$result_p = parse($p, $out);

			if($result_p->isEmpty())
			{
				break;
			}
			$r = $result_p->get();

			$value = value($r);
			$out = rest($r);

			$values[] = $value;
		}
		while($out !== "");
		return new Some(new Tuple($values, $out));
	};
}

function many_str($p)
{
	return map(many($p), 'array_to_string');
}

function many1($p)
{
	return seq2_str($p, many($p));
}

function ident()
{
	return seq2_str(lower(), many(alphanum()));
}

function nat()
{
	return map(many1(digit()), function($nr)
		{
			return intval($nr);
		}
	);
}

function space()
{
	return flatMap(many(satisfies('ctype_space')), function()
		{
			return success('');
		}
	);
}

function between_left_right($left, $right)
{
	return function($p) use ($left, $right)
	{
		return flatMap($left, function() use ($p, $right)
			{
				return flatMap($p, function($result) use ($right)
					{
						return flatMap($right, function() use ($result)
							{
								return success($result);
							}
						);
					}
				);
			}
		);
	};
}

function between($ignore)
{
	return between_left_right($ignore, $ignore);
}

function token($p)
{
	$tp = between(space());
	return $tp($p);
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
	return flatMap($p, function() { return success(''); } );
}

// helper functions

function array_to_string($arr)
{
	return implode('', $arr);
}

function flatten(array $ary)
{
	$flat = [];
	array_walk_recursive($ary, function($e) use (&$flat) { $flat[] = $e; });
	return $flat;
}

function str_head($str)
{
	return substr($str, 0, 1);
}

function str_tail($str)
{
	return substr($str, 1);
}

function str_length($str)
{
	return strlen($str);
}

function value(Tuple $t)
{
	return $t->_1();
}

function rest(Tuple $t)
{
	return $t->_2();
}

?>
