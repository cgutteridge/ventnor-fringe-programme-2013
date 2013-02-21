<?php

class Prog {

	protected $cache = array();
	protected $framework;
	protected $programme;


#GET /acts=Prog->page_acts
#GET /act/@id=Prog->page_activity
#
#GET /activities=Prog->page_activies
#GET /activity/@id=Prog->page_activity
#
#GET /event/@id=Prog->page_event
#GET /events=Prog->page_events_and_activities

	############################################################
	# custom content for Ventnor
	############################################################

	function page_wed() {
                $this->framework->set( "PARAMS.date", "2011-08-17" );
		$this->page_date();
	}
	function page_thu() {
                $this->framework->set( "PARAMS.date", "2011-08-18" );
		$this->page_date();
	}
	function page_fri() {
                $this->framework->set( "PARAMS.date", "2011-08-19" );
		$this->page_date();
	}
	function page_sat() {
                $this->framework->set( "PARAMS.date", "2011-08-20" );
		$this->page_date();
	}

        function page_about() {
                $f3=$this->framework;
		$f3->set('content','about.html');
		$f3->set('html_title','About' );
		print Template::instance()->render( "page.html" );
	}

	function place( $id ) {
		return $this->graph()->resource( "http://$_SERVER[HTTP_HOST]/2013/place/".$id );
	}

	function event( $id ) {
		return $this->graph()->resource( "http://$_SERVER[HTTP_HOST]/2013/event/".$id );
	}

	function activity( $id ) {
		return $this->graph()->resource( "http://$_SERVER[HTTP_HOST]/2013/activity/".$id );
	}

	function theme( $id ) {
		return $this->graph()->resource( "http://$_SERVER[HTTP_HOST]/2013/theme/".$id );
	}

	function programme_url() { return "vfringe2013.rdf"; }

	function site_title() { return "Ventnor Fringe 2013"; }

	############################################################
	# /custom content for Ventnor
	############################################################

        function __construct() {
                $f3=Base::instance();
                $this->framework=$f3;
        }

	############################################################
	# data retrieval functions
	############################################################

	function graph() {
		if( !isset( $this->graph ) ) {
			#$event_graph = Graphite::thaw( $filename );
			$this->graph = new Graphite();

			$this->graph->ns( "event","http://purl.org/NET/c4dm/event.owl#" );
			$this->graph->ns( "tl", "http://purl.org/NET/c4dm/timeline.owl#" );
			$this->graph->ns( "prog", "http://purl.org/prog/" );
			$n = $this->graph->load( $this->programme_url() );
		}
		return $this->graph;
	}

	function programme() {
		$programmes = $this->graph()->allOfType( "prog:Programme" );
		return $programmes[0];
	}

	function master_event() {
		return $this->programme()->get( "prog:describes","-prog:has_programme" );
	}

	function events() {
		return $this->programme()
			->all( "prog:has_event", "prog:has_sidebar_event", "prog:has_streamed_event" );
	}

	function themes() {	
		return $this->events()->all( "dcterms:subject" );
	}

	function places() {
		return $this->events()->all( "http://purl.org/NET/c4dm/event.owl#place" );
	}

	############################################################
	# page render functions
	############################################################

        function page_date() {
                $f3=$this->framework;
		$date = $f3->get( "PARAMS.date");
		$title = $this->date_render( $date )." ".$this->site_title();
		include( "programmeRenderer.php" );	
	
		$progr = new ProgrammeRenderer( $this->programme() );
		$f3->set( 'calendar', $progr->render( 
			array(
				"desc"=>"hover",
				"date"=>$date ) ));
		$f3->set('content','calendar.html');
		list($y,$m,$d) = preg_split( '/-/', $date );
		$f3->set('html_title', $title );
		print Template::instance()->render( "page.html" );
	}

        function page_homepage() {
                $f3=$this->framework;

		$f3->set('content','homepage.html');
		$f3->set('html_title', $this->site_title() );
		print Template::instance()->render( "page.html" );
	}

	# EVENTS

	# this is more of a debugging page
        function page_events_list() {
                $f3=$this->framework;
		include( "programmeRenderer.php" );	
		$progr = new ProgrammeRenderer( $this->programme() );
		$title = "All Events";
		$f3->set('content','content.html');
		$f3->set('html_content', $progr->render_list());
		$f3->set('html_title', $title );
		print Template::instance()->render( "page.html" );
	}

	function page_events() {
                $f3=$this->framework;
		$f3->set('content','events.html');
		$f3->set('html_title','Events - '.$this->site_title() );
		$f3->set('events', $this->events() );
		print Template::instance()->render( "page.html" );
	}

	function page_event() {
                $f3=$this->framework;
		$event = $this->event( $f3->get( "PARAMS.id") );
		$f3->set('event', $event );
		$f3->set('content','event.html');
		$f3->set('html_title', $event->label()." - Event - ".$this->site_title() );
		print Template::instance()->render( "page.html" );
	}

	# LOCATIONS

	function page_places() {
                $f3=$this->framework;
		$f3->set('content','places.html');
		$f3->set('html_title','Places - '.$this->site_title() );
		
		$f3->set('places', $this->places() );

		print Template::instance()->render( "page.html" );
	}

	function page_place() {
                $f3=$this->framework;
		$place = $this->place( $f3->get( "PARAMS.id") );
		$f3->set('place', $place );
		$f3->set('content','place.html');
		$f3->set('html_title', $place->label()." - Place - ".$this->site_title() );
		print Template::instance()->render( "page.html" );
	}

	# THEMES

	function page_themes() {
                $f3=$this->framework;
		$f3->set('themes', $this->themes() );
		$f3->set('content','themes.html');
		$f3->set('html_title','Themes - '.$this->site_title() );
		print Template::instance()->render( "page.html" );
	}

	function page_theme() {
                $f3=$this->framework;
		$theme = $this->theme( $f3->get( "PARAMS.id" ) );
		$f3->set('theme', $theme );
		$f3->set('content','theme.html');
		$f3->set('html_title', $theme->label()." - Theme - ".$this->site_title() );
		print Template::instance()->render( "page.html" );
	}

	# OTHER PAGE TYPES

	function page_debug() {
                $f3=$this->framework;
		$f3->set('html_content', $this->graph()->dump() );
		$f3->set('content','content.html');
		$f3->set('html_title','RDF Dump - '.$this->site_title() );
		print Template::instance()->render( "page.html" );
	}

	function page_error() {
                $f3=$this->framework;
		$f3->set('content','error.html');
		$f3->set('html_title','Error - '.$this->site_title() );
		print Template::instance()->render( "page.html" );
		exit;
	}
	
	function redir_303() {
                $f3=$this->framework;
		header("HTTP/1.1 303 See Other");
		header("Location: http://$_SERVER[HTTP_HOST]/".$f3->get("PARAMS.id"));
	}

	function date_render($date)
	{
		list($y,$m,$d) = preg_split( '/-/', $date );
		return date( "l jS \of F, Y",mktime(0,0,0,$m,$d,$y));
	}

}


