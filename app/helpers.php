<?php
//require_once(doc.'/webloit/vendor/ezyang/htmlpurifier/library/HTMLPurifier.auto.php');
use Carbon\Carbon;
use App\Providers\RouteServiceProvider;
use Illuminate\Mail\Markdown;

/*
function sanitize_html($html, $allowed_tags=null)
{
	if(!empty($allowed_tags))
	{
		$allowed_tags = strip_tags($html,$allowed_tags);
	}
	
	$config = HTMLPurifier_Config::createDefault();
	// configuration goes here:
    $config->set('Core.Encoding', 'UTF-8'); // replace with your encoding
    $config->set('HTML.Doctype', 'HTML 4.01 Transitional');	// replace with your doctype
	$config->set('Cache.DefinitionImpl', null);
    $purifier = new HTMLPurifier($config);
	$pure_html = $purifier->purify($html);
	
	return $pure_html;
}
*/

function carbon($date_time=null)
{
	return new Carbon($date_time);
}

function greatest(...$values)
{
	return collect($values)->max();
}

function trimTrailingZeroes($nbr) 
{
    return strpos($nbr,'.') !== false ? rtrim(rtrim($nbr,'0'),'.') : $nbr;
}

function model($request){
	return $request->user() ?? $request->user('staff') ?? $request->user('student') ?? false;
}

function rearrangeIndex(array &$array){
	$new_array = [];
	foreach($array as $element){
		$new_array []= $element;
	}
	$array = $new_array;
}

function numberOfDaysToWork($date_time, array $close_days)
{
	return carbon($date_time)->daysInMonth - (count($close_days) * 4);
}

function generate_id($length=5)
{
	$random_generator = openssl_random_pseudo_bytes($length);
    $id = bin2hex($random_generator);// Actual identifier.
	return $id;
}

function objectToArray($object)
{
	return json_decode(json_encode($object),true);
}

function instantiateAuthClass($guard=null)
{
	try
	{
		$class_name = get_class(request()->user($guard));
	}
	catch(\InvalidArgumentException $e)
	{
		return response("Please try again", 422);
	}
    $namespace = '\\';
    $fully_qualified_class_name = $namespace.$class_name;
    return new $fully_qualified_class_name;
}

function getSchoolOfUser()
{
	if(request()->user())
    {
        return request()->user()->school;
    }
	else if(request()->user('staff'))
	{
		return request()->user('staff')->branch->school;
	}
	else if(request()->user('student'))
	{
		return request()->user('student')->branch->school;
	}
	else
	{
		return null;
	}
}

function decideDashboard()
{
	$route = RouteServiceProvider::HOME;

	//Running artisan commands without putting this check breaks the application
	if(app()->runningInConsole()){
		return null;
	}

	switch(request()->type)
	{
		case "staff":
			return $route['staff'];
			break;

		case 'student':
			return $route['student'];
			break;

		case 'guardian':
			return $route['guardian'];
			break;

		default:
		    return $route['user'];
	}
}

function markdown($view, $data=null)
{
	$markdown = app(Markdown::class);

	return $markdown->render($view, $data)->toHtml();
}
?>