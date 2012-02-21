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
				$html .= '<li><p>'.$tweet->text.'</p>';
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
}