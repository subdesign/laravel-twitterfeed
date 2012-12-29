<?php 
/**
 * Twitterfeed - for Laravel Framework
 *
 * @author Boris Strahija (boris@creolab.hr)
 * @author Barna Szalai (info@subdesign.hu)
 * @copyright Copyright (c) 2012 Barna Szalai
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @version 1.0.0
 *
 */

class Twitter {
	
	protected static $base_url = 'http://api.twitter.com/1/';
	
	public static function timeline($username = null, $num = 5)
	{
		if ( ! $username) $username = Config::get('twitter.default_user');
		
		if ($tweets = Cache::get('twitter_timeline_'.$username.'_'.$num))
		{
			return $tweets;
		}
		else
		{
			$call_url = self::$base_url.'statuses/user_timeline.json?screen_name='.$username.'&count='.$num;
			$tweets = json_decode(file_get_contents($call_url));
			
			if($tweets)
			{
				foreach ($tweets as $key=>$tweet)
				{
					$tweets[$key]->text            = self::build_link((string) $tweet->text);
					$tweets[$key]->when            = strtotime((string) $tweet->created_at);
					$tweets[$key]->author          = (string) $tweet->user->name;
				}
				
				Cache::put('twitter_timeline_'.$username.'_'.$num, $tweets, Config::get('cache_ttl'));
				
				return $tweets;
			}
		}
		
		return null;
	        
	}
		
	public static function timeline_list($username = null, $num = 5, $return = false)
	{
		$tweets = self::timeline($username, $num);
		
		if ($tweets)
		{
			$html = '<ul class="twitter">';
			
			foreach ($tweets as $tweet)
			{
				$time = self::elapsedTime($tweet->when);

				$html .= '<li><p>'.$tweet->text;
				$html .= ' - <small>'.self::elapsedTimeString($time).'</small></p>';
				$html .= '</li>';
			}
			
			$html .= '</ul>';
			
			if ($return) return $html;
			else         echo   $html;
		}
		
		return null;
		
	} 

	public static function build_link($string = '')
	{
		$search  = array('|#([\w_]+)|', '|@([\w_]+)|');
		$replace = array('<a href="http://search.twitter.com/search?q=%23$1" target="_blank">#$1</a>', '<a href="http://twitter.com/$1" target="_blank">@$1</a>');
		$string  = preg_replace($search, $replace, $string);

		$string = " " . $string . " ";
		$string = preg_replace('/\s(http|https)\:\/\/(.+?)\s/m', '<a href="$1://$2" target="_blank">$1://$2</a>', $string);
	
		return $string;		
	}
	
	public static function elapsedTime ( $start, $end = false) {
		$returntime = array();
		
		// set defaults
		if ($end == false) {
			$end = time();
		}

		$diff = $end - $start;
		$days = floor($diff/86400); 
		$diff = $diff - ($days*86400); 

		$hours = floor ($diff/3600); 
		$diff = $diff - ($hours*3600); 

		$mins = floor ($diff/60); 
		$diff = $diff - ($mins*60); 

		$secs = $diff;

		if ($secs > 0) {
			$returntime['secs'] = $secs;
		}
		else {
			$returntime['secs'] = 0;
		}

		if ($mins > 0) {
			$returntime['mins'] = $mins;
		}
		else {
			$returntime['mins'] = 0;
		}

		if ($hours > 0) {
			$returntime['hours'] = $hours;
		}
		else {
			$returntime['hours'] = 0;
		}

		if ($days > 0) {
			$returntime['days'] = $days;
		}
		else {
			$returntime['days'] = 0;
		}

		return $returntime;
	}
	
	public static function elapsedTimeString($elapsedtime) {
		if ($elapsedtime['days'] == 0) {
			if ($elapsedtime['hours'] == 0) {
					// show minutes
				return $elapsedtime['mins'] . " minute" . (($elapsedtime['mins']>1) ? "s":"") . " ago";
			}
			else {
					// show hours
				return $elapsedtime['hours'] . " hour" . (($elapsedtime['hours']>1) ? "s":"") . " ago";
			}
		}
		else {
				// show days
			return $elapsedtime['days'] . " day" . (($elapsedtime['days']>1) ? "s":"") . " ago";
		}
	}
}
