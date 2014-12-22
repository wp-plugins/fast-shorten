<?php
/*
Plugin Name: Fast Shorten
Plugin URI: http://qass.im/my-plugins/
Description: Fast URL shortener using Google API, just one shortcode to shorten your links and hover short link to get analytics clicks.
Version: 1.0.0
Author: Qassim Hassan
Author URI: http://qass.im
License: GPLv2 or later
*/

/*  Copyright 2014  Qassim Hassan  (email : qassim.pay@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


// Add shortcode [fastshorten url="paste long url here"]
function fast_shorten_plugin_shortcode( $atts, $content = null ) {
	
	Extract(
		shortcode_atts(
			array(
				"url"		=>	home_url(), // add url attribute, default link is your home page link
				"text"		=>	"", // add text attribute, default is your blog name
				"target"	=>	"_blank", // add target attribute, default is _blank
				"rel"		=> "nofollow" // add rel attribute, default is nofollow
			),$atts
		)
	);
	
	/* Shorten URL */
	$api_array = array(
    				'headers' =>  array('content-type' => 'application/json'), // header
    				'body'    =>  '{"longUrl":"' .$url. '"}' // api field
				);
			
	$google_url_shortener_api = wp_remote_post( "https://www.googleapis.com/urlshortener/v1/url", $api_array ); // google url shortener api
	$retrieve = wp_remote_retrieve_body( $google_url_shortener_api ); // retrieve
	$response = json_decode( $retrieve, true ); // json response, this var $response['id'] is your short url
	
	if( !preg_match('/(error)|(errors)|(global)|(required)|(Required)|(parameter)|(resource.longUrl)|(400)+/', $retrieve) ){ // errors check
	
		$short_url = $response['id']; // short url
		if( empty($text) ){
			$text = $short_url;
		}
		
		/* Get Analytics */
		$url_analytics_api = wp_remote_get( "https://www.googleapis.com/urlshortener/v1/url?shortUrl=$short_url&projection=FULL" ); // google url analytics api
		$retrieve_analytics = wp_remote_retrieve_body( $url_analytics_api ); // retrieve
		$get_analytics = json_decode( $retrieve_analytics, true ); // json response
		
		if( !preg_match('/(error)|(errors)|(global)|(required)|(Required)|(parameter)|(resource.longUrl)|(400)+/', $retrieve_analytics) ){ // check errors
			$clicks = $get_analytics['analytics']['allTime']['shortUrlClicks'].' Clicks'; // short url clicks number
			return '<a rel="'.$rel.'" href="'.$short_url.'" target="'.$target.'" title="'.$clicks.'" id="fastshorten-plugin" class="fastshorten-plugin">'.$text.'</a>';
		}else{
			return '<a rel="'.$rel.'" href="'.$short_url.'" target="'.$target.'" id="fastshorten-plugin" class="fastshorten-plugin">'.$text.'</a>';
		}
		
	}
	else{
		return '<p>The long URL is error.</p>'; // error message
	}
	
}
add_shortcode("fastshorten", "fast_shorten_plugin_shortcode");

?>