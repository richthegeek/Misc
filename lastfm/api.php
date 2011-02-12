<?php
/** Complete representation of the Last.FM API un-authorised methods

@author		Richard Lyon <richthegeek@gmail.com>
@version	1.0
@license	GNU General Public License (share, modify, sell - attribution required)

Usage:	Uses magic methods to allow the format:
	lastfm->namespace->method( argument, ... );

	For example:
	$lastfm = new lastfm( API KEY );
	$lastfm->user->getInfo( "richthegeek" );

	Fun fact: old version (hard-coded, no magic methods) was over 1000 lines.
*/

class lastfm extends ParseXML
{
	// stores parameters before the request is sent
	var $params = array();
	
	// API URL, shouldn't need to be changed
	var $url = "http://ws.audioscrobbler.com/2.0/";
	
	// API Key
	var $key = false;
	
	// base = namespace, eg "album"
	var $base = false;
	// method eg "getInfo"
	var $method = false;
	
	// the method info storage.
	private $methods;

	/*  constructor( API key = false )
	 *
	 *  sets the API key if passed, and creates all the methods.
	 */
	function __construct( $key = false )
	{
		if( $key ) $this->setKey( $key );
		
		$this->methods = new stdClass;
		
		// oh god oh god why are there so many methods
		$this->add_method( "album", "getBuyLinks", "artist,album,[mbid=4],[autocorrect],[country]", "affiliations" );
		$this->add_method( "album", "getInfo", "artist,album,[mbid],[lang],[autocorrect],[username]", "album" );
		$this->add_method( "album", "getShouts", "artist,mbid,[limit],[autocorrect],[page=1]", "shouts->shout" );
		$this->add_method( "album", "getTopTags", "artist,album,[mbid],[autocorrect]", "toptags->tag"  );
		$this->add_method( "album", "search", "album,[limit=49],[page=1]", "results->albummatches->album" );

		$this->add_method( "artist", "getCorrection", "artist", "corrections->correction" );
		$this->add_method( "artist", "getEvents", "artist,[mbid],[autocorrect]", "events->event" );
		$this->add_method( "artist", "getImages", "artist,[page=1],[limit=49],[order=popularity]", "images->image" );
		$this->add_method( "artist", "getInfo", "artist,[mbid],[lang],[autocorrect],[username]", "artist" );
		$this->add_method( "artist", "getPastEvents", "artist,[mbid],[page=1],[autocorrect],[limit=49]", "events->event" );
		$this->add_method( "artist", "getPodcast", "artist,[mbid],[autocorrect]", "channel" ); 
		$this->add_method( "artist", "getShouts", "artist,[mbid],[limit],[autocorrect],[page]", "shouts->shout" );
		$this->add_method( "artist", "getSimilar", "artist,[mbid],[limit],[autocorrect]", "similarartists->artist" );
		$this->add_method( "artist", "getTopAlbums", "artist,[mbid],[autocorrect]", "topalbums->album" );
		$this->add_method( "artist", "getTopFans", "artist,[mbid],[autocorrect]", "topfans->user" );
		$this->add_method( "artist", "getTopTags", "artist,[mbid],[autocorrect]", "toptags->tag" );
		$this->add_method( "artist", "getTopTracks", "artist,[mbid],[autocorrect]", "toptracks->track" );
		$this->add_method( "artist", "search", "artist,[limit],[page]", "results->artistmatches->artist" );

		$this->add_method( "chart", "getHypedArtists", "", "artists->artist" );
		$this->add_method( "chart", "getHypedTracks", "", "tracks->track" );
		$this->add_method( "chart", "getLovedTracks", "", "tracks->track" );
		$this->add_method( "chart", "getTopArtists", "", "artists->artist" );
		$this->add_method( "chart", "getTopTags", "", "tags->tag" );
		$this->add_method( "chart", "getTopTracks", "", "tracks->track" );
		
		$this->add_method( "event", "getAttendees", "event", "attendees->user" );
		$this->add_method( "event", "getInfo", "event", "event" );
		$this->add_method( "event", "getShouts", "event", "shouts->shout" );

		$this->add_method( "geo", "getEvents", "[location],[lat],[long],[page],[distance]", "events->event" );
		$this->add_method( "geo", "getMetroArtistChart", "country,metro,[start],[end]", "topartists->artist" );
		$this->add_method( "geo", "getMetroHypeArtistChart", "country,metro,[start],[end]", "topartists->artist" );
		$this->add_method( "geo", "getMetroHypeTracksChart", "country,metro,[start],[end]", "toptracks->track" );
		$this->add_method( "geo", "getMetroTrackChart", "country,metro,[start],[end]", "toptracks->track" );
		$this->add_method( "geo", "getMetroUniqueArtistChart", "country,metro,[start],[end]", "topartists->artist" );
		$this->add_method( "geo", "getMetroUniqueTrackChart", "country,metro,[start],[end]", "toptracks->track" );
		$this->add_method( "geo", "getMetroWeeklyChartlist", "metro", "weeklychartlist->chart" );
		$this->add_method( "geo", "getMetros", "[country]", "metros->metro" );
		$this->add_method( "geo", "getTopArtists", "country", "topartists->artist" );		
		$this->add_method( "geo", "getTopTracks", "country,[location]", "toptracks->track" );		
		
		$this->add_method( "group", "getHype", "group", "weeklyartistchart->artist" );
		$this->add_method( "group", "getMembers", "group,[page],[limit]", "members->user" );
		$this->add_method( "group", "getWeeklyAlbumChart", "group,[from],[to]", "weeklyalbumchart->album" );
		$this->add_method( "group", "getWeeklyArtistChart", "group,[from],[to]", "weeklyartistchart->artist" );
		$this->add_method( "group", "getWeeklyChartList", "group", "weeklychartlist->chart" );
		$this->add_method( "group", "getWeeklyTrackList", "group,[from],[to]", "weeklytrackchart->track" );
		
		$this->add_method( "library", "getAlbums", "user,[artist],[limit],[page]", "albums->album" );
		$this->add_method( "library", "getArtists", "user,[limit],[page]", "artists->artist" );
		$this->add_method( "library", "getTracks", "user,[artist],[album],[page],[limit]", "tracks->track" );
		
		$this->add_method( "playlist", "fetch", "playlisturl", "playlist" );
		
		$this->add_method( "radio", "search", "name", "stations->station" );
		
		$this->add_method( "tag", "getInfo", "tag,[lang]", "tag" );
		$this->add_method( "tag", "getSimilar", "tag", "similartags->tag" );
		$this->add_method( "tag", "getTopAlbums", "tag", "topalbums->album" );
		$this->add_method( "tag", "getTopArtists", "tag", "topartists->artist" );
		$this->add_method( "tag", "getTopTags", "", "toptags->tag" );
		$this->add_method( "tag", "getTopTracks", "tag", "toptracks->track" );
		$this->add_method( "tag", "getWeeklyArtistChart", "tag,[from],[to],[limit]", "weeklyartistchart->artist" );
		$this->add_method( "tag", "getWeeklyChartList", "tag", "weeklychartlist->chart" );
		$this->add_method( "tag", "search", "tag,[limit],[page]", "results->tagmatches->tag" );
		
		$this->add_method( "track", "getBuyLinks", "artist,track,[mbid=4],[autocorrect],[country]", "affiliations" );
		$this->add_method( "track", "getCorrection", "artist,track", "corrections->correction->track" );
		$this->add_method( "track", "getFingerprintMetadata", "fingerprintid", "tracks->track" );
		$this->add_method( "track", "getInfo", "artist,track,[mbid],[lang],[autocorrect],[username]", "track" );
		$this->add_method( "track", "getShouts", "artist,track,[mbid],[limit],[autocorrect],[page=1]", "shouts->shout" );
		$this->add_method( "track", "getSimilar", "artist,track,[mbid],[autocorrect],[limit]", "similartracks->track" );
		$this->add_method( "track", "getTopFans", "artist,track,[mbid],[autocorrect]", "topfans->user" );
		$this->add_method( "track", "getTopTags", "artist,track,[mbid],[autocorrect]", "toptags->tag"  );
		$this->add_method( "track", "search", "track,[artist],[limit=49],[page=1]", "results->trackmatches->track" );
		
		$this->add_method( "user", "getArtistTracks", "user,artist,[starttimestamp],[page],[endtimestamp]", "artisttracks->track" );
		$this->add_method( "user", "getBannedTracks", "user,[limit],[page]", "bannedtracks->track" );
		$this->add_method( "user", "getEvents", "user", "events->event" );
		$this->add_method( "user", "getFriends", "user,[recenttracks],[limit],[page]", "friends->user" );
		$this->add_method( "user", "getInfo", "user", "user" );
		$this->add_method( "user", "getLovedTracks", "user,[limit],[page]", "lovedtracks->track" );
		$this->add_method( "user", "getNeighbours", "user,[limit]", "neighbours->user" );
		$this->add_method( "user", "getNewReleases", "user,[userecs]", "albums->album" );
		$this->add_method( "user", "getPastEvents", "user,[page],[limit]", "events->event" );
		$this->add_method( "user", "getPersonalTags", "user,tag,[taggingtype],[limit],[page]", "taggings->artists->artist" );
		$this->add_method( "user", "getPlaylists", "user", "playlists->playlist" );
		$this->add_method( "user", "getRecentTracks", "user,[limit],[page],[to],[from]", "recenttracks->track" );
		$this->add_method( "user", "getShouts", "user", "shouts->shout" );
		$this->add_method( "user", "getTopAlbums", "user,[period]", "topalbums->album" );
		$this->add_method( "user", "getTopArtists", "user,[period]", "topartists->artist" );
		$this->add_method( "user", "getTopTags", "user,[limit]", "toptags->tag" );
		$this->add_method( "user", "getTopTracks", "user,[period]", "toptracks->track" );
		$this->add_method( "user", "getWeeklyAlbumChart", "user,[from],[to]", "weeklyalbumchart->album" );
		$this->add_method( "user", "getWeeklyArtistChart", "user,[from],[to]", "weeklyartistchart->artist" );
		$this->add_method( "user", "getWeeklyChartList", "user", "weeklychartlist->chart" );
		$this->add_method( "user", "getWeeklyTrackChart", "user,[from],[to]", "weeklytrackchart->track" );
		
		$this->add_method( "venue", "getEvents", "venue", "events->event" );
		$this->add_method( "venue", "getPastEvents", "venue,[page],[limit]", "events->event" );
		$this->add_method( "venue", "search", "venue,[page],[limit],[country]", "results->venuematches->venue" );		
		// that took fucking ages to type
	
	}
	
	/* add_method( namespace, method, arguments, path )
	 *
	 * see constructor for example usage.
	 */
	private function add_method( $namespace, $method, $arguments, $path )
	{
		// if the namespace isn't instantiated, do so
		if( !isset( $this->methods->$namespace ) )
		{
			$this->$namespace = new lastfm_sub( $this, $namespace );
			$this->methods->$namespace = new stdClass;
		}
		
		// new method object	
		$o = new stdClass;
		$o->arguments = array();
		$o->minimum = 0;
		
		// parse the arguments list
		foreach( explode( ",", $arguments ) as $argument )
		{
			// if no arguments this occurs, so skip it to relieve E_NOTICE
			if( ! strlen( $argument ) ) continue;
			
			// is it an optional argument?
			$optional = ( $argument{0} == "[" );
			if( $optional ) $argument = substr( $argument, 1, -1 );
			if(!$optional ) $o->minimum++;
			
			// does it have a default (functionally redundant)
			$default = false;
			if( strpos( $argument, "=" ) )
				list( $argument, $default ) = explode( "=", $argument );
			
			// create the argument object
			$a = new stdClass;
			$a->optional = $optional;
			$a->argument = $argument;
			$a->default  = $default;
			
			// add to argument list
			$o->arguments[] = $a;
		}
		$o->argument_string = $arguments;
		$o->path = $path;
		
		// add to method list
		$this->methods->$namespace->$method = $o;
	}
	
	// set the API key
	private function setKey( $key )
	{
		$this->key = $key;
		$this->addParam( "api_key", $key );
	}
	
	// clear all parameters except the api key
	private function clearParams()
	{
		$this->params = array();
		$this->addParam( "api_key", $this->key );
	}
	
	// add parameter "key" to value "val"
	private function addParam( $key, $val )
	{
		$this->params[ $key ] = $val;
	}
	
	// execute request
	private function get()
	{
		$params  = array( "method=".$this->base.".".$this->method );
		foreach( $this->params as $k=>$v ) $params[] = urlencode($k)."=".urlencode($v);
		$pstring = "?" . implode( "&" , $params );
		return $this->from_url( $this->url.$pstring );
	}
	
	// passthrough from the namespaced magic __call
	function call( $namespace, $method, $arguments )
	{
		// if there is no such namespace, die
		if( ! isset( $this->methods->$namespace ) )
			die( "Unknown namespace LastFM::$namespace" );
		
		// if there is no such method in this namespace, die
		if( ! isset( $this->methods->$namespace->$method ) )
			die( "Unknown method LastFM::$namespace::$method" );

		$info = $this->methods->$namespace->$method;

		// if the number of arguments is less than the number of requried arguments, die
		if( count( $arguments ) < $info->minimum )
			die( "Not enough arguments passed to LastFM::$namespace::$method" );
	
		$this->base = $namespace;
		$this->method = $method;
		
		$this->clearParams();
	
		// add arguments to request			
		foreach( $arguments as $i=>$argument )
			$this->addParam( $info->arguments[ $i ]->argument, $argument );

		$result = @ $this->get();

		// parse path				
		foreach( explode( "->", $info->path ) as $key )
			$result = $result->$key;
		
		return $result;
	}
	
	# outputs sparse documentation based on the data available (optionally filtered by namespace, method)
	function document( $namespace = false, $method = false, $sub = false )
	{
		if( ! $namespace )
		{
			$return = "<h1>LastFM API</h1><ul>";
			foreach( $this->methods as $namespace=>$methods )
				$return .= "\n".$this->document( $namespace, false, true );
			return $return."</ul>";
		}				
	
		if( ! isset( $this->methods->$namespace ) )
			return "No such namespace $namespace";
			
		if( ! $method )
		{
			$return = ($sub ? "<li>" : "") . "<h2>".$namespace."</h2>\n<ul>";
			foreach( $this->methods->$namespace as $method=>$options )
				$return .= "\n\n".$this->document( $namespace, $method, true );
			return $return."</li></ul>".($sub?"</li>":"");
		}
		
		if( ! isset( $this->methods->$namespace->$method ) )
			return "No such method $namespace->$method";			
		
		if( ! $sub ) $html = "<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;lastfm->$namespace->$method( ".str_replace(",", ", ", $this->methods->$namespace->$method->argument_string)." )</p>";
		else $html = "<li>$method( ".str_replace(",", ", ", $this->methods->$namespace->$method->argument_string)." )</li>";
		
		return $html;
	}

}

// handles the crossover from __get()->__call() in the most elegant way I can muster
class lastfm_sub
{
	var $parent = false;
	var $name = false;
	
	function __construct( $parent, $name )
	{
		$this->parent = & $parent;
		$this->name = $name;
	}
	
	function __call( $method, $arguments )
	{
		return $this->parent->call( $this->name, $method, $arguments );
	}
}

// parses results using SimpleXML
class ParseXML
{	
	function from_url( $url )
	{
		return ($load = @ file_get_contents( $url )) ? $this->from_string( $load ) : false;
	}
	
	function from_file( $file )
	{
		return ($load = @file_get_contents( "./".$file )) ? $this->from_string( $load ) : false;
	}
	
	function from_string( $source )
	{
		return $this->to_stdClass( (object) simplexml_load_string( $source ) );
	}
	
	function to_stdClass( $source )
	{
		$new = new stdClass;

		foreach( $source as $name=>$child )
		{	
			$n = count( $child ) ? $this->to_stdClass( $child ) : (string) $child;
			
			if( ! count( $child ) && count( $child->attributes() ) )
			{
				$n = $this->to_stdClass_attr( $child );
				$n->value = $child->asXML();
			}
				
			if( isset($new->$name) )
			{
				if( !is_array( $new->$name ) ) $new->$name = array( $new->$name );
				$new->{$name}[] = $n;
				continue;
			}
			$new->$name = $n;
		}
		
		return $new;
	}
	
	function to_stdClass_attr( $source )
	{
		$new = new stdClass;
		foreach( $source->attributes() as $k=>$v ) $new->$k = (string) $v;
		return $new;
	}
}

/* $l = new lastfm();
print $l->document( ); */
