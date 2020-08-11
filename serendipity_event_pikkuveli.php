<?php
/**
 * Pikkuveli
 * 
 * PHP version 7.3
 * 
 * Pikkuveli ("little brother") time tracking application for Serendipity
 * 
 * @category	Integration
 * @package		Pikkuveli
 * @author		Mauri "daFool" Sahlberg <mauri.sahlberg@gmail.com>
 * @copyright	2020 Mauri Sahlberg, Helsinki
 * @license		BSD-2 https://opensource.org/licenses/BSD-2-Clause
 * @link		https://github.com/daFool/pikkuveli
 */

 if (IN_serendipity !== true) {
    die ("Don't hack!");
}
@serendipity_plugin_api::load_language(dirname(__FILE__));

/**
 * serendipity_event_pikkuveli
 * 
 * Interface class for Serendipity
 * 
 * @category	Integration
 * @package		Pikkuveli
 * @author		Mauri "daFool" Sahlberg <mauri.sahlberg@gmail.com>
 * @copyright	2020 Mauri Sahlberg, Helsinki
 * @license		BSD-2 https://opensource.org/licenses/BSD-2-Clause
 * @link		https://github.com/daFool/pikkuveli
 */
 
class serendipity_event_pikkuveli extends serendipity_event {
	/**
	 * @var array $pikkuveliMessages	Message-array
	 */
	private $pikkuveliMessages;
	
	/**
	 * Introspect
	 * 
	 * Apparently called when "plugin" is presented for user in plugin-list. And probably by Spartacus as well.
	 * @param array &$propbag	Propertybag
	 */
	function introspect(&$propbag) {
		$propbag->add('name',         	PLUGIN_PIKKUVELI_TITLE);
		$propbag->add('description',   	PLUGIN_PIKKUVELI_DESC);
		$propbag->add('stackable',		false);
		$propbag->add('requirements',  array(
			'serendipity' => '0.9.1',
			'smarty'      => '2.6.7',
			'php'         => '4.1.0'
		));
		$propbag->add('version',      	'0.6');
		$propbag->add('author',       	'Mauri "daFool" Sahlberg (mauri.sahlberg@gmail.com)');
		$propbag->add('event_hooks',	array(
											'backend_display' => true,
											'backend_publish'=>true,
											'backend_save'=>true));

		$propbag->add('groups', 		array('BACKEND_EDITOR'));
		$propbag->add('configuration',	array(
											PLUGIN_PIKKUVELI_LASTEDIT_START_PROPERTY,
											PLUGIN_PIKKUVELI_LASTEDIT_END_PROPERTY,
											PLUGIN_PIKKUVELI_LASTEDIT_LAST_PROPERTY,
											PLUGIN_PIKKUVELI_TOTAL_PROPERTY
										)
		);

		$this->dependencies = array('serendipity_event_entryproperties' => 'keep');
	}

	/**
	 * introspect_config_item
	 * 
	 * Called on plugin configuration
	 * @param string 	$name	Configuration item
	 * @param array		&$propbag	Propertybag
	 */
	function introspect_config_item($name, &$propbag) {
		switch ($name) {
			case PLUGIN_PIKKUVELI_LASTEDIT_START_PROPERTY:
				$propbag->add('type', 'string');
				$propbag->add('name', PLUGIN_PIKKUVELI_LASTEDIT_START_PROPERTY);
				$propbag->add('description', PLUGIN_PIKKUVELI_LASTEDIT_START_DESC);
				$propbag->add('default', '');
				break;
			case PLUGIN_PIKKUVELI_LASTEDIT_END_PROPERTY:
				$propbag->add('type', 'string');
				$propbag->add('name', PLUGIN_PIKKUVELI_LASTEDIT_END_PROPERTY);
				$propbag->add('description', PLUGIN_PIKKUVELI_LASTEDIT_END_DESC);
				$propbag->add('default', '');
				break;
			case PLUGIN_PIKKUVELI_LASTEDIT_LAST_PROPERTY:
				$propbag->add('type', 'string');
				$propbag->add('name', PLUGIN_PIKKUVELI_LASTEDIT_LAST_PROPERTY);
				$propbag->add('description', PLUGIN_PIKKUVELI_LASTEDIT_LAST_DESC);
				$propbag->add('default', '');
				break;
			case PLUGIN_PIKKUVELI_TOTAL_PROPERTY:
				$propbag->add('type', 'string');
				$propbag->add('name', PLUGIN_PIKKUVELI_TOTAL_PROPERTY);
				$propbag->add('description', PLUGIN_PIKKUVELI_TOTAL_DESC);
				break;
		}
		return true;
	}

	/** 
	 * Install
	 * 
	 * Creates table for timestamps. Called from plugin install.
	 * 	@return true
	*/
	function install() {
		global $serendipity;

		$q = "CREATE TABLE {$serendipity['dbPrefix']}pikkuveli_stamps (
    		entry_id 	INTEGER NOT NULL,
    		starts 		varchar(20) NOT NULL,
			ends		varchar(20) NOT NULL,
    		seconds		bigint NOT NULL,
    		comment		text);";
		serendipity_db_schema_import($q);
		return true;
	}

	/** 
	 * Uninstall
	 * 
	 * Drops stamp-table, called from plugin uninstall.
	 * @return true
	 */
	function uninstall(&$propbag) {
		global $serendipity;

		$q = "DROP TABLE {$serendipity['dbPrefix']}pikkuveli_stamps;";
		serendipity_db_schema_import($q);
		return true;
	}

	/** 
	 * Returns unix-localtime-compatible seconds
	 * 
	 * @param string	$ts Timestamp in "YYYY-MM-DD HH[:.]MM[:.]SS" format.
	 * 
	 * @return either seconds or null if timestamp did not match the format
	*/
	function mytimestamp2unixtime($ts) {
		@list($date,$time) = explode(' ',$ts);
		if(is_null($date)||is_null($time)) {
			return null;
		}
		@list($year,$month,$day)=explode('-',$date);
		if(is_null($year)||is_null($month)||is_null($day)) {
			return null;
		}
		$time=str_replace(':','.',$time);
		@list($hour,$min,$second)=explode('.',$time);
		if(is_null($hour) || is_null($min) || is_null($second)) {
			return null;
		}
		$uts = mktime($hour,$min,$second,$month,$day,$year);
		return $uts;
	}

	/** Updates extended entry-properties
	 * 
	 * Requires extended properties to work and that the extended properties have
	 * two customfields that are named as Pikkuveli fields with same name. 
	 * 
	 * @param $last The latest edit of entry
	 * @param $total Total seconds spent at editing the entry
	*/
	function fixEntryProps($last, $total) {
		global $serendipity;
		
		$to = $this->get_config(PLUGIN_PIKKUVELI_TOTAL_PROPERTY);
		if(is_null($to) || $to=="") {
			return;
		}
		$serendipity['POST']['properties'][$to]=serendipity_db_escape_string($this->toHours($total));
		
		$st = $this->get_config(PLUGIN_PIKKUVELI_LASTEDIT_START_PROPERTY);
		if(is_null($st) || $st=="") {
			return;
		}
		$serendipity['POST']['properties'][$st]=serendipity_db_escape_string($last['start']);
		
		$st = $this->get_config(PLUGIN_PIKKUVELI_LASTEDIT_END_PROPERTY);
		if(is_null($st) || $st=="") {
			return;
		}
		$serendipity['POST']['properties'][$st]=serendipity_db_escape_string($last['end']);
		
		$st = $this->get_config(PLUGIN_PIKKUVELI_LASTEDIT_LAST_PROPERTY);
		if(is_null($st) || $st=="") {
			return;
		}
		$serendipity['POST']['properties'][$st]=serendipity_db_escape_string($last['last']);
		
		return;
	}

	/** Builds the lastedit stamp
	* @param $start "YYYY-MM-DD HH.MM.SS"
	* @param $end "YYYY-MM-DD HH.MM.SS"
	* @param $seconds Seconds spent in editing 
	* @return array
	*/
	function buildLast($start,$end,$seconds) {
		$last=array();
		$last['start']=$start;
		$last['end']=$end;
		$last['last']=$this->toHours($seconds);
		return $last;
	}
	 
	/** Fetches timestamps associated with this entry
	* @param $e_id	Entry identifier
	* @return $result['rivit'] array of timestamps in format "Start date+time#End date+time#Comment#seconds"
	* @return $result['summa'] sum of seconds spent in editing this entry
	* @return $result['last'] latest edit of this entry in format "start-end time spent"
	*/
	
	function fetchOldies($e_id) {
		global $serendipity;
		
		$oldies=array();
		$result=array();
		$summa=0;
		$result['summa']=0;
		$result['rivit']=array();
		$result['last']="";
		if(is_null($e_id) || $e_id=="") {
			return $result;
		}
		$eid = serendipity_db_escape_string($e_id);
		$q = "select * from {$serendipity['dbPrefix']}pikkuveli_stamps where entry_id=$eid order by starts desc;";
		$res = serendipity_db_query($q);
		
		if(is_array($res)) {
			$first=true;
			foreach($res as $row) {
				$summa += $row['seconds'];
				$starts= $row['starts'];
				$ends = $row['ends'];
				$comment = $row['comment'];
				$line = "$starts#$ends#$comment#".$row['seconds'];
				array_push($oldies, $line);
				if($first) {
					$result['last']=$this->buildLast($starts,$ends,$row['seconds']);
					$first=false;
				}
			}
			$result['rivit']=$oldies;
			$result['summa']=$summa;
		}
		// print_r($result);
		return $result;
	}
	
	/** Formats seconds to time
	* @param $seconds to format
	* @return "HH.MM.SS"
	*/
	function toHours($seconds) {
		$minutes = floor($seconds/60);
		$hours = floor($minutes/60);
		$minutes-=$hours*60;
		$seconds-=($minutes*60+$hours*60*60);
		return sprintf("%02d.%02d.%02d",$hours,$minutes,$seconds);
	}
							
	/** Backend display of article, a.k.a. start editing
	*/
	function startStamp($e_id) {
		global $serendipity;

		$offset = ($serendipity['serverOffsetHours']??0)*60*60;
		$hasid="nope";
		/* Is this the first edit? */
		if(!is_null($e_id)) {
			/* Nope, update entryprops */
			$result = $this->fetchOldies($e_id);
			$this->fixEntryProps($result['last'],$result['summa']);
			$hasid="yup";
		}
		
		$ts = date('Y-m-d H.i.s', time()+$offset);
		/* Build input-fields */
		require('pikkuveli_form.php');
		return;
	}

	/** Delete one timestamp from the database
	* @param $todelete Stamp to delete in format "start#end#comment#seconds"
	*/
	function deleteStamp($todelete) {
		global $serendipity;
		
		list($start, $end, $comment,$seconds)=explode('#',$todelete);
		$start = serendipity_db_escape_string($start);
		$end = serendipity_db_escape_string($end);
		$comment = serendipity_db_escape_string($comment);
		$seconds = serendipity_db_escape_string($seconds);
		
		$q = "delete from {$serendipity['dbPrefix']}pikkuveli_stamps where 
			starts='$start' and ends='$end' and comment='$comment' and seconds=$seconds;";
		if(serendipity_db_query($q)) {
			$this->pikkuveliMessages.=sprintf(PLUGIN_PIKKUVELI_DELETED,$todelete);
		}
		
		return;
	}
	
	/** Backend publish a.k.a. save 
	* @param $e_id Entry id
	*/
	function endStamp($e_id) {
		global $serendipity;
		
		$offset = ($serendipity['serverOffsetHours']??0)*60*60;
		$this->pikkuveliMessages="";
		
		/* Just to get the necessary variables. */
		$res = $this->fetchOldies($e_id);
		
		/* End of the edit, a.k.a. current time and date */
		$ets = date('Y-m-d H.i.s', time()+$offset);
		
		/* Start of the edit, from the hidden input field pikkuveli_start generated on backend-display */
		$sts = serendipity_db_escape_string($serendipity['POST']['pikkuveli_start']);
		
		/* Should we record this edit or not? Checkbox pikkuveli_stampit on backend-display */
		$stampit = $serendipity['POST']['pikkuveli_stampit']=='on' ? false : true;
		$noid = $serendipity['POST']['pikkuveli_noid']=='nope' ? true : false;
		
		/* Comment string for automatically generated timestamps */
		$c = PLUGIN_PIKKUVELI_AUTO;
		$s = $this->mytimestamp2unixtime($sts);
		$e = $this->mytimestamp2unixtime($ets);
		if(is_null($e) || is_null($s)) {
			$sum=0;
		}
		$sum = $e-$s;
		$eid = serendipity_db_escape_string($e_id);
		
		if($stampit) {
			$q = "INSERT into {$serendipity['dbPrefix']}pikkuveli_stamps 
				(entry_id,starts,ends,seconds,comment)
				values ($eid,'$sts','$ets',$sum,'$c');";
			serendipity_db_query($q);		
			$res['summa']+=$sum;
			$res['last']=$this->buildLast($sts,$ets,$sum);
			$this->fixEntryProps($res['last'],$res['summa']);
		}
		if($noid && $stampit) {
			echo PLUGIN_PIKKUVELI_KLUDGE_YES;
		}
		if($noid && !$stampit) {
			echo PLUGIN_PIKKUVELI_KLUDGE_NO;
		}
		/* Where there something selected on pikkuveli_oldones? This means that we should delete it. */
		$todelete = $serendipity['POST']['pikkuveli_oldones'];
		if($todelete !="") {
			$this->deleteStamp($todelete);
			$res=$this->fetchOldies($e_id);
		}
		
		/* Where there something in the manual stamping fields or not? */
		$sta = $serendipity['POST']['pikkuveli_begin'];
		$ste = $serendipity['POST']['pikkuveli_end'];
		$c = $serendipity['POST']['pikkuveli_comment'];
		$s = $this->mytimestamp2unixtime($sta);
		$e = $this->mytimestamp2unixtime($ste);
		$co = serendipity_db_escape_string($c);
		if(is_null($e) || is_null($s)) {
			/* Either there were not or the timestamps were bad. */
			$this->fixEntryProps($res['last'],$res['summa']);
			return;
		}
		/* There were, let's add that one. */
		$sum = $e-$s;
		$q = "INSERT into {$serendipity['dbPrefix']}pikkuveli_stamps 
			(entry_id,starts,ends,seconds,comment)
			values ($eid,'$sta','$ste',$sum,'$co');";
		serendipity_db_query($q);		
		$stamp="$sta#$ste#$co#$sum";
		$this->pikkuveliMessages.=sprintf(PLUGIN_PIKKUVELI_ADDED,$stamp);
		$result = $this->fetchOldies($e_id);
		$this->fixEntryProps($result['last'],$result['summa']);
		return;
	}

	/** Don't know where this is called and don't care. Set's title */
	function generate_content(&$title) {
		$title = PLUGIN_PIKKUVELI_TITLE;
	}

	/**
	 * Guess which one?
	*  @param $e_id	Entry id
	*  @return true 
	*/
	function doDisplay($e_id) {
		global $serendipity;
		
		if(is_array($serendipity['POST'])) {
			if($serendipity['POST']['adminAction']=='save') {
				If($serendipity['POST']['pikkuveli_noid']=='yup') {
					$this->endStamp($e_id);
				}
				$this->startStamp($e_id);
				return true;
			}
			else {
				$this->startStamp($e_id);
				return true;
			}
		}
		$this->startStamp($e_id);
		return true;
	}
	
	/** Serendipity's plugin event hook. We should be called after extended properties to
	* get things to work properly. Unfortunately I know no method to ensure that nor way to
	* ensure that user install us twice, which also will break things...
	*/
	function event_hook($event, &$bag, &$eventData, $addData = null) {
		global $serendipity;
		$hooks = &$bag->get('event_hooks');

		if (isset($hooks[$event])) {
			switch($event) {
				case 'backend_display':
					return $this->doDisplay($eventData['id']);
				case 'backend_publish':
				case 'backend_save':
					if($serendipity['POST']['pikkuveli_noid']=='nope') {
						return $this->endStamp($eventData['id']);
					}
					return true;
			}
		}
		return false;
	}
}

/* vim: set sts=3 ts=4 expandtab : */
?>
