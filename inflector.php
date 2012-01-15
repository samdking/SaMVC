<?php

abstract class Inflector
{
   
	static function pluralise($word)
	{	   
		// e.g. cry -> cries
		if (substr($word, -1) == 'y')
			if (!in_array(substr($word, -2, 1), array('a', 'e', 'i', 'o', 'u')))
				return substr($word, 0, -1) . 'ies';
		
		// e.g. bench -> benches, mash -> mashes
		if (in_array(substr($word, -2), array('ch', 'sh')))
			return $word . 'es';
		
		// e.g. miss -> misses, fizz -> fizzes, fix -> fixes
		if (in_array(substr($word, -1), array('s', 'z', 'x')))
			return $word . 'es';
		
		// e.g. knife -> knives	
		if (substr($word, -2) == 'fe')
			return substr($word, 0, -2) . 'ves';
		
		// e.g. crisis -> crises
		if (substr($word, -2) == 'is')
			return substr($word, 0, -2) . 'es';

		// already plural
		if (substr($word, -1) == 's')
			return $word;
			
		// e.g. dog -> dogs
		return $word . 's';
	}
	
	static function singularise($word)
	{
		// e.g. babies -> baby
		if (substr($word, -3) == 'ies')
			if (strlen($word) > 4)
				return substr($word, 0, -3) . 'y';
				
		// e.g. faxes -> fax
		if (substr($word, -3) == 'xes' && strlen($word) > 4)
			return substr($word, 0, -2);
		
		// e.g. banjoes -> banjo
		if (substr($word, -3) == 'oes' && strlen($word) > 4)
			return substr($word, 0, -2);
			
		// e.g. dogs -> dog
		if (substr($word, -1) == 's')
			return substr($word, 0, -1);
		
		// not plural
		return $word;
	}
}