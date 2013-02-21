<?php
class ProgrammeRenderer {

	protected $source_url;
	protected $programme_uri;
	protected $graph;
	protected $prog;
	protected $master_event;
	
	function __construct( $programme )
	{
		$this->graph = $programme->g;
		$this->prog = $programme;
		$this->graph->ns( "prog","http://purl.org/prog/" );
		$this->graph->ns( "progx","http://purl.org/prog/testing/" );
		$this->graph->ns( "ev","http://purl.org/event/" );
		$this->graph->ns( "event","http://purl.org/NET/c4dm/event.owl#" );
		$this->graph->ns( "bio","http://purl.org/vocab/bio/0.1/" );
		$this->graph->ns( "spatialrelations", "http://data.ordnancesurvey.co.uk/ontology/spatialrelations/" );
		$this->graph->ns( "tl", "http://purl.org/NET/c4dm/timeline.owl#" );
	
		$this->master_event = $this->prog->get( "prog:describes","-prog:has_programme" );
		
		foreach( $this->graph->allOfType( "tl:Interval" ) as $interval )
		{
			if( $interval->has( "tl:start" ) )
			{
				foreach( $interval->all( "-event:time" ) as $event )
				{
					if( ! $event->has( "ev:dtstart" ) )
					{
						$this->graph->addTurtle( '_:', "<".$event->toString()."> <http://purl.org/event/dtstart> \"".$interval->get( "tl:start" )->toString()."\" ." );
					}
				}
			}
	
			if( $interval->has( "tl:end" ) )
			{
				foreach( $interval->all( "-event:time" ) as $event )
				{
					if( ! $event->has( "ev:dtend" ) )
					{
						$this->graph->addTurtle( '_:', "<".$event->toString()."> <http://purl.org/event/dtend> \"".$interval->get( "tl:end" )->toString()."\" ." );
					}
				}
			}
		}
	}


	###############################
	# LIST
	###############################

	function render_list()
	{
		$events = $this->prog->all( "prog:has_event","prog:has_streamed_event" )->sort( "ev:dtstart" );
		$day = "";
		$lstart = "";

		$output = array();
		$output []= "<ul class='programme_list'>";
		$started = 0;
		foreach( $events as $event )
		{
			$dtstart = $event->get( "ev:dtstart" )->toString();
			$dtend = $event->get( "ev:dtend" )->toString();
			$thisday = substr( $dtstart, 0, 10 );
			$endday = substr( $dtend, 0, 10 );
			$thisstart = substr( $dtstart, 11, 5 );
			$endend = substr( $dtend, 11, 5 );
			#list( $thisday, $thisstart , $duff ) = preg_split( "/[TZ]/", $event->get( "ev:dtstart" )->toString() );
			#list( $duff2, $thisend , $duff ) = preg_split( "/[TZ]/", $event->get( "ev:dtend" )->toString() );
			if( $thisday != $day )
			{
				if( $started ) { $output []= "</ul></li></ul></li>"; }
				$started = 1;
				$output []= "<li>$thisday";
				$output []= "<ul><li>$thisstart<ul>";
				$lstart = "";
			}
			elseif( $thisstart != $lstart ) 
			{
				$output []= "</ul></li>";
				$output []= "<li>$thisstart<ul>";
			}
			$lstart = $thisstart;
			$day = $thisday;
			$output []= "<li>".$event->label()." @ ".$event->get( "ev:located", "event:place" )->label()."</li>";
		}
		if( $started ) { $output []= "</ul></li></ul></li>"; }
		$output []= "</ul>";
		return join( '', $output );
	}

	###############################
	# /LIST
	###############################
	
	###############################
	# ICAL
	###############################

	function serve_ical()
	{
		header( "content-type: text/calendar" );
		$events = $this->prog->all( "prog:describes","-prog:has_programme", "prog:has_event","prog:has_streamed_event" )->sort( "ev:dtstart" );
		print $this->list_to_ical( $events );
	}

	static function ical_escape($text)
	{
        	$text = strip_tags($text);
        	$rep_array = array(
                	'\\'    =>  '\\\\',
                	','      =>  '\,',
                	';'      =>  '\;',
                	"\r"    =>  '',
                	"\n"    =>  '\n'
        	);
        	$text = str_replace(array_keys($rep_array), array_values($rep_array), $text);
        	return $text;
	}

	static function ical_split_long_lines($text)
	{
        	$parts = str_split($text, 70);
        	$line = array_shift($parts);
        	foreach ($parts as $part) {
                	$part = utf8_encode($part);
                	$line .= "\r\n $part";
        	}
        	return $line;
	}

	protected function list_to_ical( $events )
	{
        	$lines = array(
                	'BEGIN:VCALENDAR',
                	'VERSION:2.0',
                	'X-WR-CALNAME:X123',
                	'PRODID:-//XMLEVENT//Events//EN',
                	'X-WR-TIMEZONE:Europe/London',
                	'CALSCALE:GREGORIAN',
                	'METHOD:PUBLISH',
                	'BEGIN:VTIMEZONE',
                	'TZID:Europe/London',
                	'BEGIN:DAYLIGHT',
                	'TZOFFSETFROM:+0000',
                	'RRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=-1SU',
                	'DTSTART:19810329T010000',
                	'TZNAME:GMT+01:00',
                	'TZOFFSETTO:+0100',
                	'END:DAYLIGHT',
                	'BEGIN:STANDARD',
                	'TZOFFSETFROM:+0100',
                	'RRULE:FREQ=YEARLY;BYMONTH=10;BYDAY=-1SU',
                	'DTSTART:19961027T020000',
                	'TZNAME:GMT',
                	'TZOFFSETTO:+0000',
                	'END:STANDARD',
                	'END:VTIMEZONE',
                	);
        	foreach( $events as $event )
        	{
                	$lines[] = $this->to_ical_vevent( $event );
        	}
        	$lines[] = 'END:VCALENDAR';
        	return implode("\r\n", $lines);
	}

	protected function to_ical_vevent( $event )
	{
        	$dstart = preg_split( '/[- :TZ]/', $event->get( "ev:dtstart" )->toString() );
        	$dend = preg_split( '/[- :TZ]/', $event->get( "ev:dtend" )->toString() );
		$start_line = "";	
		$end_line = "";	
		if( sizeof( $dstart ) >= 5 )
		{
        		$d_format = 'TZID=Europe/London:%04d%02d%02dT%02d%02d%02d';
        		$start_line = "DTSTART;".sprintf($d_format, $dstart[0],$dstart[1],$dstart[2],$dstart[3],$dstart[4], 0);
        		$end_line = "DTEND;".sprintf($d_format, $dend[0],$dend[1],$dend[2],$dend[3],$dend[4], 0);
		}
		if( sizeof( $dstart ) == 3 )
		{
        		$d_format = 'VALUE=DATE:%04d%02d%02d';
        		$start_line = "DTSTART;".sprintf($d_format, $dstart[0],$dstart[1],$dstart[2] );
			$endday = mktime( 0,0,0,$dend[1],$dend[2],$dend[0] );
        		$end_line = "DTEND;"."VALUE=DATE:".date('Ymd', $endday + 24*60*60 );
		}
	
        	$title = self::ical_escape($event->label());
        	$summary = self::ical_split_long_lines("SUMMARY:$title");
	
        	#$description = ical_escape($event['cal:description'][0]);
        	#$description_line = ical_split_long_lines("DESCRIPTION:$description");
        	$location_line = null;
		if( $event->has( "ev:located", "event:place" ) )
		{
			$location_line = 'LOCATION:' . self::ical_escape($event->get( "ev:located", "event:place" )->label());
		}
	
        	$lines[] = 'BEGIN:VEVENT';
        	$lines[] = "UID:".self::ical_escape( $event->toString() );
        	$lines[] = $start_line;
        	$lines[] = $end_line;

        	$lines[] = $summary;
        	#$lines[] = $description_line;
        	#$lines[] = 'URL:' . $this->data['url'];
	
        	if (!is_null($location_line)) {
                	$lines[] = $location_line;
        	}
        	$lines[] = "END:VEVENT";
	
        	return implode("\r\n", $lines);
	}

	
	###############################
	# /ICAL
	###############################

	private static function render_calendar_day( $times, $grid_col_widths, $grid, $graph, $date, $opts )
	{
		$timelist = $times[$date];	
		ksort( $timelist );
		array_pop( $timelist );
	
		$r = "";
		$r.= "<table class='programme_table'>";
	
		if( !isset( $grid_col_widths["main"][$date] ) ) # skip if only one col!
		{
			$r.= "<tr>";
			foreach( $grid_col_widths as $col=>$width )
			{
				if( $col == "timeslot" ) 
				{ 
					$title= ""; 
				}
				elseif( $col == "main" ) 
				{ 
					$title= "Event"; 
				}
				else
				{
					$title = $graph->resource( $col )->label();
			}
				
				if( isset( $width[$date] ) )
				{
					$r.= "<td class='programme_stream_title' colspan='".$width[$date]."'>$title</td>";
				}
			}
			$r.= "</tr>";	
		}
	
		foreach( $timelist as $time=>$null )
		{
			$r.= "<tr>";
		foreach( $grid_col_widths as $col=>$width )
			{
				$first_col_rendered = 0;
				if( !isset( $grid[$col][$date] )) { continue; }
				foreach( $grid[$col][$date] as $a=>$v )
				{
					if( !isset( $v[$time] ) ) { $r.= "<td>&nbsp;</td>"; continue; }
					$event = $v[$time];
					#if( !isset( $event ) && !$first_col_rendered) { $r.= "<td>x</td>"; }
					#if( !isset( $event ) ) { $r.= "<td>$col.$a,$time</td>"; continue; }
					if( $event == "FILLED" ) { continue; }
	
					$thing = $event["thing"];
					$class=$event["class"];
					$start_t = self::dt_to_timet( $thing->get( "ev:dtstart" )->toString() );
					$end_t = self::dt_to_timet( $thing->get( "ev:dtend" )->toString() );
					if( $end_t > time() && $start_t < time() ) { $class .= ' programme_current'; }
	
					$r.= "<td class='$class' rowspan='".$event["rowspan"]."' colspan='".$event["colspan"]."'>";
					if( $end_t > time() && $start_t < time() ) {
						$r .= "<div class='programme_now_message'>now</div>";
					}
					$r.= "<div class='programme_cell_content'>";
			
					if( $thing->has("foaf:page") ) { $r.= '<a href="'.$thing->get("foaf:page")->toString().'">'; }
					if( !$thing->has( "rdfs:label" ) && $thing->has( "prog:realises" ) )
					{
						$r.= $thing->get( "prog:realises" )->label();
					}
					else
					{
						$r.= $thing->label();
					}
					if( $thing->has("foaf:page") ) { $r.= '</a>'; }
					if( $thing->has( "progx:timeNote" ) )
					{
						$r.= "<div style='font-size:90%'>(".$thing->getString( "progx:timeNote" ).")</div>";
					}
	
	
					if( isset( $event["debug"] ) )	
					{
						$r.= "<div><small>Debug: ".$event["debug"]."</small></div>";
					}
	#$r.=$thing->get("event:place")->label();
					if( $thing->has( "event:agent" ) )
					{
						foreach( $thing->all( "event:agent" ) as $agent )
						{
							$r.= render_agent( $agent, $opts );
						}
					}
					if( $thing->has( "dct:description", "bibo:abstract" ) )
					{
						$desc = $thing->get( "dct:description", "bibo:abstract" )->toString(); 
						if( $opts["desc"]  == 'show' ) 
						{
							$r.='<div class="programme_session_desc">'.$desc.'<div>';
						}
						#TODO toggle, hover
					}

					if( $thing->has( "rdfs:seeAlso" ) )
					{
						$r.="<div class='programme_session_seealso'>See also:<ul>";
						foreach( $thing->all( "rdfs:seeAlso" ) as $seealso )
						{
							$r.="<li>";
							$r.="<a href='".$seealso->toString()."'>".$seealso->label()."</a>";
							$r.="</li>";
						}
						$r.="</ul></div>";
					}
	
					$loc = $event["thing"]->get( "ev:located", "event:place" );
					if( 0&& $loc->has( "geo:lat" ) )
					{
						$long = $loc->get( "geo:long" )->toString();
						$lat = $loc->get( "geo:lat" )->toString();
						$r.= " <span style='font-size:80%'>[<a href='http://maps.google.com/maps?q=$lat,$long'>map</a>]</span>";
					}
	
					$r.= "</div>\n";
		
					$r.= "</td>\n";
	
					$first_col_rendered = 1;
				}
			}		
			$r.= "</tr>\n";
		}
		$r.= "</table>\n";
		return $r;
	}

	
	private static function add_to_grid( $thing, $times, &$column, &$column_width, $class )
	{
		if( !$thing->has( "ev:dtstart" ) ) { return; }
		if( !$thing->has( "ev:dtend" ) ) { return; }
	
		$start = self::dt_to_datetime( $thing->get( "ev:dtstart" )->v );
		$end = self::dt_to_datetime( $thing->get( "ev:dtend" )->v, true );
		# don't render things which start/end on different days
		if( $start["date"] != $end["date"] ) { return; } 
		if( $start["time"] > $end["time"] ) 
		{
			error_log( $thing->uri." has start time after end time!" );
			return;
		}
		$slots = array();
		if( isset( $times[$start["date"]] ) )
		{
			foreach( $times[$start["date"]] as $time=>$null ) 
			{
				if( $time >= $start["time"] && $time < $end["time"] )
				{
					$slots []= $time;
				}
			}
		}
		$sub_col = 0;
		$ok = 0;
		while( !$ok )
		{
			$ok = 1;
			foreach( $slots as $slot )
			{
				if( isset($column[$start["date"]][$sub_col][$slot]) ) { $ok = 0; }
			}
			if( $ok ) { break; }
			$sub_col += 1;
		}
		if( !isset( $column_width[$start["date"]] ) || $column_width[$start["date"]]<$sub_col+1 ) 
		{ 
			$column_width[$start["date"]] = $sub_col+1; 
		}
		$column[$start["date"]][$sub_col][$start["time"]] = array( "thing"=>$thing, "rowspan"=>sizeof($slots), "colspan"=>1, "class"=>$class );
		
		if( isset( $times[$start["date"]] ) )
		{
			foreach( $times[$start["date"]] as $time=>$null ) 
			{
				if( $time > $start["time"] && $time < $end["time"] )
				{
					$column[$start["date"]][$sub_col][$time] = "FILLED";
				}
			}
		}
	}
	
	private static function add_times( $thing, &$times )
	{
		if( $thing->has( "ev:dtstart" ) ) { self::add_time( $thing->get( "ev:dtstart" )->v, $times ); }
		if( $thing->has( "ev:dtend" ) ) { self::add_time( $thing->get( "ev:dtend" )->v, $times, true ); }
	}
	
	private static function dt_to_datetime( $dt, $endtime=false )
{
		if( !preg_match( '/^((\d\d\d\d)-(\d\d)-(\d\d))T(\d\d:\d\d(?::\d\d)?)/', $dt, $bits ) ) # ignore timezone
		{
			error_log( "Bad DateTime: $dt" );
			return;
		}
		if( strlen( $bits[5] ) == 5 ) { $bits[5] .= ":00"; }
		if( $bits[5] == '00:00:00' && $endtime )
		{
			$bits[1] = sprintf( "%04d-%02d-%02d", $bits[2], $bits[3], $bits[4]-1 );
			$bits[5] = "24:00:00";
		}
		$a =  array( "date"=>$bits[1], "time"=>$bits[5] );
		return $a;
	}
	
	private static function add_time( $dt, &$times, $endtime=false )
	{
		$datetime = self::dt_to_datetime( $dt, $endtime );
		if( !isset( $datetime ) )
		{
			error_log( "Bad DateTime: $dt" );
			return;
		}
		$times[$datetime["date"]][$datetime["time"]] = 1;
	}
	
	private static function style()
{
		return '
<style>
.programme_table
{
	border-collapse: collapse;
}
.programme_session
{
	background-color: #fbfdfd;
	background-image: url(http://programme.ecs.soton.ac.uk/bluewash.png);
	background-repeat: repeat-x;
}
.programme_unstreamed_session
{
	background-color: #ffffcc;
}
.programme_timeslot
{
	background-color: #ffffff;
	background-image: url(http://programme.ecs.soton.ac.uk/greywash.png);
	background-repeat: repeat-x;
}
.programme_stream_title, .programme_session, .programme_unstreamed_session, .programme_timeslot
{
	border: solid 1px black;
	vertical-align: top;
	padding: 0px;
}
.programme_stream_title
{
	background-color: #cccccc;
	background-image: url(http://programme.ecs.soton.ac.uk/greywash.png);
	background-repeat: repeat-x;
	text-align: center;
	padding: 0.5em;
}
.programme_cell_content {
	text-align: center;
	vertical-align: top;
	padding: 0.5em;
}
.programme_session_agent
{
	font-size:80%;
	margin-top:0.5em;
}
.programme_session_desc
{
	text-align: left;
	font-size:80%;
	margin-top:0.5em;
}
.programme_session_seealso
{
	margin-top:0.5em;
}
.programme_session_seealso ul
{
	margin-top: 0.1em;
}
.programme_current .programme_cell_content {
}
.programme_now_message 
{
	text-align: center;
	background-color: #fc3;
	color: #900;
	font-size: 80%;
}
	
	
</style>';
}

	private static function render_agent($agent,$opts)
	{
		$r="<div class='programme_session_agent'>";
		if( $agent->has("foaf:homepage") )
		{
			$r.= '<a href="'.$agent->get("foaf:homepage")->toString().'">';
		}
		$r.= $agent->label();
		if( $agent->has("foaf:homepage") )
		{
			$r.= '</a>';
		}
		foreach( $agent->all( "-foaf:primaryTopic" ) as $doc )
		{
			foreach( $doc->all( "rdf:type" ) as $type )
			{
				if( $type->toString() == 'http://xmlns.com/foaf/0.1/PersonalProfileDocument' )
				{
					$r .= " [<a href='".$doc->toString()."'>FOAF</a>]";
				}
			}
		}
	
		if( $agent->has("-foaf:member" ) )
		{
			$list = array();
			foreach( $agent->all("-foaf:member") as $org )
			{
				$item = "";
				if( $org->has("foaf:homepage") )
				{
					$item.= '<a href="'.$org->get("foaf:homepage")->toString().'">';
				}
				$item.= $org->label();
				if( $org->has("foaf:homepage") )
				{
					$item.= '</a>';
				}
				$list[]=$item;
			}
			$r.=" (".join( ", ", $list ).")";
		}
		if( $agent->has( "bio:biography" ) && $opts["bio"] == "inline")
		{
			$bio = $agent->get( "bio:biography" );
		
			if( $bio->nodeType() == "http://purl.org/xtypes/Fragment-HTML" )
			{
				$r.="<div style='text-align:left'>".$bio->toString()."</div>";
			}
			else
			{
				$r.="<div style='text-align:left'>".htmlspecialchars($bio->toString())."</div>";
			}
		}
			
		$r.="</div>";
		return $r;
	}
	
	private static function dt_to_timet( $dt )
	{
		if( !preg_match( '/^(\d\d\d\d)-(\d\d)-(\d\d)T(\d\d):(\d\d)(?::(\d\d))?/', $dt, $bits ) ) # ignore timezone
		{
			error_log( "Bad DateTime: $dt" );
			return;
		}
		if( sizeof( $bits ) == 6 ) { $bits[6] = "00";  }
		return mktime( $bits[4], $bits[5], $bits[6], $bits[2], $bits[3], $bits[1] );
	}


	# opts:
	#  desc = toggle |
	#  bio =
	function render( $opts = array() )
	{
		if( !isset($opts["desc"] ) ) { $opts["desc"]  = "toggle"; } # show hide hover toggle
	
		# assume everything is in the same timezone!
		
		$times = array();
		foreach( $this->prog->all( "prog:has_timeslot" ) as $timeslot )
		{
			self::add_times( $timeslot, $times );
		}
		foreach( $this->prog->all( "prog:has_streamed_event" ) as $timeslot )
		{
			self::add_times( $timeslot, $times );
		}
		foreach( $this->prog->all( "prog:has_event" ) as $timeslot )
		{
			self::add_times( $timeslot, $times );
		}
		# times now contains all the start end times of all events

		if( ! $this->prog->has( "prog:has_timeslot" ) )
		{
			$ttl = "";
			foreach( $times as $day=>$daytimes )
			{
				$list = array_keys( $daytimes );
				sort( $list );
				
				for( $i=0;$i<sizeof($list)-1;++$i )
				{
					$uri = "_#timeslot-$day-".$list[$i];
					$ttl.= "<$uri> <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> <http://purl.org/event/TimeSlot> . \n";
					if( $i == sizeof($list)-2 )
					{
						$ttl.= "<$uri> <http://www.w3.org/2000/01/rdf-schema#label> \"".substr($list[$i],0,5)."-".substr($list[$i+1],0,5)."\" .\n";
					}
					else
					{
						$ttl.= "<$uri> <http://www.w3.org/2000/01/rdf-schema#label> \"".substr($list[$i],0,5)."\" .\n";
					}
					$ttl.= "<$uri> <http://purl.org/event/dtstart> \"".$day."T".$list[$i]."Z\" .\n";
					$ttl.= "<$uri> <http://purl.org/event/dtend> \"".$day."T".$list[$i+1]."Z\" .\n";
					$ttl.= "<".$this->prog->toString()."> <http://purl.org/prog/has_timeslot> <$uri> .\n";
				}
			
			}
			$this->graph->addTurtle( '_:', $ttl );
		}
		
		$grid = array();
		$grid_col_widths = array();
		foreach( $this->prog->all( "prog:has_timeslot" ) as $timeslot )
		{
			self::add_to_grid( $timeslot, $times, $grid["timeslot"], $grid_col_widths["timeslot"], "programme_timeslot" );
		}
			
	
		foreach( $this->prog->all( "prog:has_streamed_event" ) as $event )
		{
			foreach( $this->prog->all( "prog:streamed_by_subject" ) as $subject )
			{
				$matches = 0;
				foreach( $event->all( "dct:subject" ) as $event_subject)
				{
					if( $event_subject->toString() == $subject->toString() )
				{
						self::add_to_grid( $event, $times, $grid[$subject->uri], $grid_col_widths[$subject->uri], "programme_session" );
					}
				}
			}
			foreach( $this->prog->all( "prog:streamed_by_location" ) as $location )
			{
				$matches = 0;
				foreach( $event->all( "ev:located", "event:place" ) as $event_location)
				{
					if( $event_location->toString() == $location->toString() )
					{
						self::add_to_grid( $event, $times, $grid[$location->uri], $grid_col_widths[$location->uri], "programme_session" );
					}
				}
			}
		
			foreach( $this->prog->all( "prog:streamed_by_parent_event" ) as $parent_event )
			{
				$matches = 0;
				foreach( $event->all( "dct:isPartOf" ) as $event_parent_event)
				{
					if( $event_parent_event->toString() == $parent_event->toString() )
					{
						self::add_to_grid( $event, $times, $grid[$parent_event->uri], $grid_col_widths[$parent_event->uri], "programme_session" );
					}
				}
			}
		}
		
		$datewidth = array();
		foreach( $times as $date=>$timedata )
		{
			$datewidth[$date] = 0;
			foreach( $grid as $stream_id => $stream )
			{
				if( $stream_id == "timeslot" ) { continue; }
				if( isset( $stream[$date] ) ) { $datewidth[$date]++; }
			}
			if( $datewidth[$date] == 0 )
			{
				$grid["main"][$date] = array( '0'=>array());
				$grid_col_widths["main"][$date] = 1;
			}
		}
		# add the non-streamed events
		foreach( $this->prog->all( "prog:has_event" ) as $event )
		{
			$start = dt_to_datetime( $event->get( "ev:dtstart" )->toString() );
			$end = dt_to_datetime( $event->get( "ev:dtend" )->toString(), true );
		
			if( !isset( $start ) ) { continue; } # could log in debug/validator mode?) 
	
			if( $datewidth[$start["date"]] == 0 )
			{
				self::add_to_grid( $event, $times, $grid['main'], $grid_col_widths['main'], "programme_session" );
				continue;
			}
		
			$slots = array();
			foreach( $times[$start["date"]] as $time=>$null ) 
			{
				if( $time >= $start["time"] && $time < $end["time"] )
				{
					$slots []= $time;
				}
			}
			$ok = 1;
			$cell = array( "thing"=>$event, "colspan"=>0, "rowspan"=>0, "class"=>"programme_unstreamed_session" );
		
			#$column[$start["date"]][$sub_col][$start["time"]] = array( thing=>$thing, rowspan=>sizeof($slots), colspan=>1 );
			foreach( $grid as $stream_id => $stream )
			{
				if( $stream_id == "timeslot" ) { continue; }
				if( !isset( $stream[$start["date"]] ) ) { continue; }
				foreach( $stream[$start["date"]] as $subcol_id=>$subcol )
				{
						foreach( $slots as $slot )
				{
						if( isset($grid[$stream_id][$start["date"]][$subcol_id][$slot]) ) { $ok = 0; break 3; }
						if( $cell["rowspan"] == 0 && $cell["colspan"] == 0 ) 
						{
							$first_stream_id = $stream_id;	
							$first_slot = $slot;	
							$first_subcol_id = $subcol_id;
						}
						if( $cell["colspan"] == 0 ) { $cell["rowspan"]+=1; }
					}
					$cell["colspan"]+=1;
				}
			}
		
			# $ok means that there's no filled slots and we can replace the individual entries in each columnm
			# with a full width single cell. Much nicer looking.
			if( $ok )
			{
				foreach( $grid as $stream_id => $stream )
				{
					if( $stream_id == "timeslot" ) { continue; }
					if( !isset( $stream[$start["date"]] ) ) { continue; }
					foreach( $stream[$start["date"]] as $subcol_id=>$subcol )
					{
						foreach( $slots as $slot )
						{
							$grid[$stream_id][$start["date"]][$subcol_id][$slot] = "FILLED";
						}
					}
				}
				$grid[$first_stream_id][$start["date"]][$first_subcol_id][$first_slot] = $cell;
			}
			else
			{
				# put them in every column.
				$prev_stream_id = "";
				$prev_subcol_id = "";
				$prev_start_slot = "";
				$prev_rows = "";
				foreach( $grid as $stream_id => $stream )
				{
					if( $stream_id == "timeslot" ) { continue; }
					if( !isset( $stream[$start["date"]] ) ) { continue; }
					foreach( $stream[$start["date"]] as $subcol_id=>$subcol )
					{
						$slot_i = 0;
						while( $slot_i < sizeof( $slots ) )
						{
							$start_slot = $slots[$slot_i];
							$rows = 0;
							while( $slot_i < sizeof( $slots ) && !isset( $grid[$stream_id][$start["date"]][$subcol_id][$slots[$slot_i]] ) )
							{
								$grid[$stream_id][$start["date"]][$subcol_id][$slots[$slot_i]] = "FILLED";
								++$slot_i;
								++$rows;
							}
							
							if( $rows )
							{
								if( $rows == $prev_rows && $start_slot == $prev_start_slot )
								{
									$grid[$prev_stream_id][$start["date"]][$prev_subcol_id][$prev_start_slot]["colspan"]++;
								}
								else
								{
									$grid[$stream_id][$start["date"]][$subcol_id][$start_slot] = array( "thing"=>$event, "colspan"=>1, "rowspan"=>$rows, "class"=>"programme_unstreamed_session" );
									$prev_stream_id = $stream_id;
									$prev_subcol_id = $subcol_id;
									$prev_start_slot = $start_slot;
								}
							}
							$prev_rows = $rows;
		
							++$slot_i;
						}
					}
				}
			}
		}
		
		if( isset($opts["date"]) )
		{
			return self::style().self::render_calendar_day( $times, $grid_col_widths, $grid, $this->graph, $opts["date"], $opts );
		}

		$title = $this->prog->label();
		if( !$this->prog->hasLabel() )
		{
			$title = "Programme for ".$this->prog->get("prog:describes","-prog:has_programme")->label();
		}
	
		$output = array();		
		$output []= self::style();
		$output []= "<h1>$title</h1>";
		ksort( $times );
		foreach( $times as $date=>$timelist ) 
		{
			list($y,$m,$d) = preg_split( '/-/', $date );
			$output []= "<h2>".date( "l jS \of F, Y",mktime(0,0,0,$m,$d,$y))."</h2>";
			$output []= self::render_calendar_day( $times, $grid_col_widths, $grid, $graph, $date , $opts);
		}
		$output []= "<p><small>Rendering of programmed data from <a href='$src'>$src</a> in the <a href='http://programme.ecs.soton.ac.uk/1.0/'>Event Programme Ontology</a>.</small></p>";
		return join( '', $output );
	}
	
		
}
		
