<?php

require 'init.php';

$start_time = microtime();
/*
$general_courses_in_city = Model::get('city')
	->equal('slug','brighton')
	->schools()
		->courses()
			->select('name')
			->include_rel('school')
			->course_types(false)
				->equal('name', 'General');

$language_in_countries = Model::get('language')
	->equal('slug', 'english')
	->countries()
		->order('destination_en ASC');
*/
	
/*
$school = new School;
$languages_of_general_courses_taught_at_specific_school = $school->courses()->course_types(false)->equal('name','General')->languages();
*/
/*
$course_type = new Course_type;
$languages = $course_type->courses()->languages();
*/

/*
$course_types = Model::get('language')->courses()->course_types();
*/
?>

<html>
	<head>
		<title>Model Testing</title>
		<style type="text/css">
			pre { display: inline; background: #DEE; padding: 1px 0; font-family: Courier; font-size: 12px; line-height: 1.6em }
			pre:after { content:''; clear: both; display:block }
			pre.error { background: #EEBBBB }
			body { border: 5px solid #DDD; border-width: 0 8px; width: 50%; padding: 15px; margin: auto }
			#times { background: #333; padding: 6px 8px; color: #FFF; font-size: 3em; text-align: center; font-family: Georgia; margin: 15px -40px 0px; border: 1px solid #FFF }
			.obj .obj { max-width: 150px; border-style: dotted; outline: 0 }
			.obj { vertical-align: top; outline: 2px solid #eee; border: 1px solid #000; font-family: 'Monaco'; padding: 5px; display: inline-block; min-width: 60px; font-size: 9px; margin: 5px; min-height: 60px }
			hr { margin: 15px -50%; border: 0; border-top: 5px dotted #AAA; width: 200% }
		</style>
	</head>
	<body>
<?php
/*
$general_courses_in_city = Model::get('course')
	->filter(array(
		'course_type.name' => 'General', 
		'school.city.slug' => 'brighton', 
		'language.language_en in' => array('Spanish', 'German')
	))
	->select('*', 'language.language_en', 'school.name');
	
foreach($general_courses_in_city as $course) {
	echo $course;
	echo $course->school;
}

echo '<hr />';

$language_in_countries = Model::get('country')
	->filter(array('languages.slug'=>'english'))
	->sort('destination_en ASC');

foreach($language_in_countries as $lang)
	echo $lang;
	
echo '<hr />';

$languages_of_general_courses_taught_at_specific_school = Model::get('language')
	->filter(array('courses.course_type.name' => 'General', ));

foreach($languages_of_general_courses_taught_at_specific_school as $lan)
	echo $lan;

echo '<hr />';

$languages = Model::get('language')
	->filter(array('courses.course_type.id'=>2));

foreach($languages as $language)
	echo $language;

echo '<hr />';

$course_types = Model::get('course_type')
	->filter(array('courses.language.language_en'=>'Spanish'));

echo $course_types->count();

foreach($course_types as $ct)
	echo $ct;
	
echo '<hr />';

$course = Model::get('course')->find(1);
echo $course;

echo '<hr />';

$schools = Model::get('school')->select('*', 'city.destination_en')->find(1,2,3);
foreach($schools as $school)
	echo $school;

echo '<hr />';

$brighton_schools = Model::get('school')->filter(array('city.destination_en'=>'Brighton'));
foreach($brighton_schools as $school)
	echo $school;
	*/
echo '<hr />';

	$url_parts = array_values(array_filter(explode('/', '/languages/spanish/uk/brighton')));
	echo '<h3>Grab the language</h3>';
	$language = Model::get('language')->find(array('slug'=>$url_parts[1]));
	
	echo '<h3>Grab the city</h3>';
	try {
		$city = Model::get('city')->select('*', 'country.destination_en', 'country.id')->find(array('slug'=>$url_parts[3], 'country.slug'=>$url_parts[2]));
	} catch (RecordNotFoundException $e) {
		echo 'city not found';
	}
	
	echo '<h3>Grab the country</h3>';
	$country = $city->country;
	
	echo $country;
	echo $city;
	
	$city = ($parent = $city->parent)? $parent : $city;
	
	if ($parent)
		$extra = array($parent->destination_en => $parent->getURL($language->slug, $country->slug), str_replace(', ' . $parent->destination_en, '', $city->destination_en) => NULL);
	else
		$extra = array($city->destination => NULL);
		
	foreach($country->course_types() as $course_type)
		echo $course_type;
		
	if ($city->sub_cities)
		echo 'this city has sub sectors';
		
	//$courses = $city->courses(array('language_id'=>$language->id));
	$courses = Model::get('course')->filter(array('language_id'=>$language->id));
	
	$course_types = Model::get('course_type')->sort('sort_order');
	foreach($courses as $course) {
		if (!$course->lowest_price()) continue;
			$types[$course->course_type_id][] = $course;
	}
	
	echo '<h3>Inserting into Schools table</h3>';
	
	//$city = Model::get('city');
	//$country = Model::get('country');
	//$city->destination_en = 'Berlin';
	//$country->destination_en = 'Germany';
	//$city->country = $country;
	//echo $city;
	//$city->save();
	
	//$city = Model::get('city')->find(array('destination_en'=>'Cambridge'));
	//$city->country = Model::get('country')->find(array('slug'=>'uk'));
	//$city->save();
	
	$school = Model::get('school')->find(1);
	foreach($school->courses as $course)
		echo $course;

?>

	<div id="times"><?=round(microtime()-$start_time, 6)?> <em>secs</em></div>
	</body>
</html>