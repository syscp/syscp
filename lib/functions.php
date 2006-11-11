<?php
/**
 * This file is part of the SysCP project.
 * Copyright (c) 2003-2006 the SysCP Team (see authors).
 *
 * For the full copyright and license information, please view the COPYING
 * file that was distributed with this source code. You can also view the
 * COPYING file online at http://files.syscp.org/misc/COPYING.txt
 *
 * @copyright  (c) the authors
 * @author     Florian Lippert <flo@syscp.org>
 * @license    GPLv2 http://files.syscp.org/misc/COPYING.txt
 * @package    Functions
 * @version    $Id$
 */

	/**
	 * Get template from filesystem
	 *
	 * @param string Templatename
	 * @param string noarea If area should be used to get template
	 * @return string The Template
	 * @author Florian Lippert <flo@redenswert.de>
	 */
	function getTemplate($template, $noarea = 0)
	{
		global $templatecache;
		if($noarea != 1)
		{
			$template = AREA.'/'.$template;
		}

		if(!isset($templatecache[$template]))
		{
			$filename = './templates/'.$template.'.tpl';
			if(file_exists($filename))
			{
				$templatefile=addcslashes(file_get_contents($filename), '"\\');
			}
			else
			{
				$templatefile='<!-- TEMPLATE NOT FOUND: '.$filename.' -->';
			}
//			$templatefile = preg_replace("'<if ([^>]*?)>(.*?)</if>'si", "\".( (\\1) ? \"\\2\" : \"\").\"", $templatefile);
			$templatefile = preg_replace('/<if[ \t]*(.*)>(.*)(<\/if>|<else>(.*)<\/if>)/Uis','".( ($1) ? ("$2") : ("$4") )."', $templatefile);
			$templatecache[$template] = $templatefile;
		}
		return $templatecache[$template];
	}

	/**
	 * Prints one ore more errormessages on screen
	 *
	 * @param array Errormessages
	 * @param string A %s in the errormessage will be replaced by this string.
	 * @author Florian Lippert <flo@redenswert.de>
	 * @author Ron Brand <ron.brand@web.de>
	 */
	function standard_error($errors='', $replacer='')
	{
		global $db, $tpl, $userinfo, $s, $header, $footer, $lng;

		$replacer = htmlentities( $replacer );

		if(!is_array($errors))
		{
			$errors = array ($errors);
		}

		$error = '';
		foreach ($errors as $single_error)
		{
			if(isset($lng['error'][$single_error]))
			{
				$single_error = $lng['error'][$single_error];
				$single_error = strtr($single_error, array('%s' => $replacer));
			}
			else
			{
				$error = 'Unknown Error';
				break;
			}

			if(empty($error))
			{
				$error = $single_error;
			}
			else
			{
				$error .= ' ' . $single_error;
			}
		}
		eval("echo \"".getTemplate('misc/error','1')."\";");
		exit;
	}

	/**
	 * Returns HTML Code for two radio buttons with two choices: yes and no
	 *
	 * @param string Name of HTML-Variable
	 * @param string Value which will be returned if user chooses yes
	 * @param string Value which will be returned if user chooses no
	 * @param string Value which is chosen by default
	 * @return string HTML Code
	 * @author Florian Lippert <flo@redenswert.de>
	 */
	function makeyesno($name,$yesvalue,$novalue="",$yesselected="")
	{
		global $lng;
		if($yesselected)
		{
			$yeschecked=' checked="checked"';
			$nochecked='';
		}
		else
		{
			$yeschecked='';
			$nochecked=' checked="checked"';
		}
		$code="<b>".$lng['panel']['yes']."</b> <input type=\"radio\" name=\"$name\" value=\"$yesvalue\"$yeschecked /> &nbsp; \n<b>".$lng['panel']['no']."</b> <input type=\"radio\" name=\"$name\" value=\"$novalue\"$nochecked /> ";
		return $code;
	}

	/**
	 * Prints Question on screen
	 *
	 * @param string The question
	 * @param string File which will be called with POST if user clicks yes
	 * @param array Values which will be given to $yesfile. Format: array(variable1=>value1, variable2=>value2, variable3=>value3)
	 * @param string Name of the target eg Domain or eMail address etc.
	 * @author Florian Lippert <flo@redenswert.de>
	 */
	function ask_yesno ( $text , $yesfile , $params = array() , $targetname = '')
	{
		global $userinfo , $tpl , $db , $s , $header , $footer , $lng ;

/*
		// For compatibility reasons (if $params contains a string like "field1=value1;field2=value2") this will convert it into a usable array
		if(!is_array($params))
		{
			$params_tmp=explode(';',$params);
			unset($params);
			$params=array();
			while(list(,$param_tmp)=each($params_tmp))
			{
				$param_tmp=explode('=',$param_tmp);
				$params[$param_tmp[0]]=$param_tmp[1];
			}
		}
*/

		$hiddenparams='';
		if(is_array($params))
		{
			foreach($params as $field => $value )
			{
				$hiddenparams.='<input type="hidden" name="'.htmlspecialchars($field).'" value="'.htmlspecialchars($value).'" />'."\n";
			}
		}

		if ( isset ( $lng['question'][$text] ) )
		{
			$text = $lng['question'][$text] ;
		}
		$text = strtr( $text, array('%s' => $targetname ) );

		eval ( "echo \"".getTemplate('misc/question_yesno','1')."\";" ) ;
	}

	/**
	 * Return HTML Code for an option within a <select>
	 *
	 * @param string The caption
	 * @param string The Value which will be returned
	 * @param string Values which will be selected by default.
	 * @param bool Whether the title may contain html or not
	 * @param bool Whether the value may contain html or not
	 * @return string HTML Code
	 * @author Florian Lippert <flo@redenswert.de>
	 */
	function makeoption($title,$value,$selvalue=NULL,$title_trusted=false,$value_trusted=false)
	{
		if($selvalue !== NULL && $value==$selvalue)
		{
			$selected='selected="selected"';
		}
		else
		{
			$selected='';
		}
		if (!$title_trusted)
		{
			$title=htmlspecialchars($title);
		}
		if (!$value_trusted)
		{
			$value=htmlspecialchars($value);
		}
		$option='<option value="'.$value.'" '.$selected.' >'.$title.'</option>';
		return $option;
	}

	/**
	 * Sends an header ( 'Location ...' ) to the browser.
	 *
	 * @param   string   Destination
	 * @param   array    Get-Variables
	 * @param   boolean  if the target we are creating for a redirect
	 *                   should be a relative or an absolute url
	 *
	 * @return  boolean  false if params is not an array
	 *
	 * @author  Florian Lippert <flo@redenswert.de>
	 * @author  Martin Burchert <eremit@syscp.org>
	 *
	 * @changes martin@2005-01-29
	 *          - added isRelative parameter
	 *          - speed up the url generation
	 *          - fixed bug #91
	 */
	function redirectTo ( $destination , $get_variables = array(), $isRelative = false )
	{
		$params = array();
		if ( is_array ( $get_variables ) )
		{
			foreach( $get_variables as $key => $value )
			{
				$params[] = urlencode($key) . '=' . urlencode($value);
			}

			$params = '?' . implode($params, '&' );

			if ( $isRelative )
			{
				$protocol = '';
				$host     = '';
				$path     = './';
			}
			else
			{
				if ( isset( $_SERVER['HTTPS'] ) && strtolower($_SERVER['HTTPS']) == 'on' )
				{
					$protocol = 'https://';
				}
				else
				{
					$protocol = 'http://';
				}
				$host = $_SERVER['HTTP_HOST'];

				if ( dirname( $_SERVER['PHP_SELF'] ) == '/' )
				{
					$path     = '/';
				}
				else
				{
					$path     = dirname($_SERVER['PHP_SELF']) . '/';
				}
			}
			header ( 'Location: ' . $protocol . $host . $path . $destination . $params ) ;
			exit;
		}

		return false;
	}

	/**
	 * Returns Array, whose elements have been checked whether thay are empty or not
	 *
	 * @param array The array to trim
	 * @return array The trim'med array
	 * @author Florian Lippert <flo@redenswert.de>
	 */
	function array_trim($source)
	{
		$returnval = array();
		if(is_array($source))
		{
			while(list($var,$val)=each($source))
			{
				if($val!=' ' && $val!='') $returnval[$var]=$val;
			}
		}
		else
		{
			$returnval=$source;
		}
		return $returnval;
	}

	/**
	 * Replaces Strings in an array, with the advantage that you
	 * can select which fields should be str_replace'd
	 *
	 * @param mixed String or array of strings to search for
	 * @param mixed String or array to replace with
	 * @param array The subject array
	 * @param string The fields which should be checked for, separated by spaces
	 * @return array The str_replace'd array
	 * @author Florian Lippert <flo@redenswert.de>
	 */
	function str_replace_array($search, $replace, $subject, $fields = '')
	{
		if(is_array($subject))
		{
			$fields = array_trim(explode(' ', $fields));

			foreach( $subject as $field => $value )
			{
				if( ( !is_array($fields) || empty($fields) ) || (is_array($fields) && !empty($fields) && in_array($field, $fields) ) )
				{
					$subject[$field] = str_replace($search, $replace, $subject[$field]);
				}
			}
		}
		else
		{
			$subject = str_replace($search, $replace, $subject);
		}
		return $subject;
	}

	/**
	 * Wrapper around htmlentities to handle arrays, with the advantage that you
	 * can select which fields should be handled by htmlentities
	 *
	 * @param array The subject array
	 * @param string The fields which should be checked for, separated by spaces
	 * @param int See php documentation about this
	 * @param string See php documentation about this
	 * @return array The array with htmlentitie'd strings
	 * @author Florian Lippert <flo@redenswert.de>
	 */
	function htmlentities_array($subject, $fields = '', $quote_style = ENT_COMPAT, $charset = 'ISO-8859-1')
	{
		if(is_array($subject))
		{
			if( !is_array( $fields ) )
			{
				$fields = array_trim(explode(' ', $fields));
			}

			foreach( $subject as $field => $value )
			{
				if( ( !is_array($fields) || empty($fields) ) || (is_array($fields) && !empty($fields) && in_array($field, $fields) ) )
				{
					/**
					 * Just call ourselve to manage multi-dimensional arrays
					 */
					$subject[$field] = htmlentities_array( $subject[$field], $fields, $quote_style, $charset );
				}
			}
		}
		else
		{
			$subject = htmlentities( $subject, $quote_style, $charset );
		}
		return $subject;
	}

	/**
	 * Wrapper around html_entity_decode to handle arrays, with the advantage that you
	 * can select which fields should be handled by htmlentities and with advantage,
	 * that you can eliminate all html entities by setting complete=true
	 *
	 * @param array The subject array
	 * @param string The fields which should be checked for, separated by spaces
	 * @param bool Select true to use html_entity_decode_complete instead of html_entity_decode
	 * @param int See php documentation about this
	 * @param string See php documentation about this
	 * @return array The array with html_entity_decode'd strings
	 * @author Florian Lippert <flo@redenswert.de>
	 */
	function html_entity_decode_array($subject, $fields = '', $complete = false, $quote_style = ENT_COMPAT, $charset = 'ISO-8859-1')
	{
		if(is_array($subject))
		{
			if( !is_array( $fields ) )
			{
				$fields = array_trim(explode(' ', $fields));
			}

			foreach( $subject as $field => $value )
			{
				if( ( !is_array($fields) || empty($fields) ) || (is_array($fields) && !empty($fields) && in_array($field, $fields) ) )
				{
					/**
					 * Just call ourselve to manage multi-dimensional arrays
					 */
					$subject[$field] = html_entity_decode_array( $subject[$field], $fields, $complete, $quote_style, $charset );
				}
			}
		}
		else
		{
			if( $complete == true )
			{
				$subject = html_entity_decode_complete( $subject, $quote_style, $charset );
			}
			else
			{
				$subject = _html_entity_decode( $subject, $quote_style, $charset );
			}
		}
		return $subject;
	}

	/**
	 * Wrapper around stripslashes to handle arrays, with the advantage that you
	 * can select which fields should be handled by htmlentities and with advantage,
	 * that you can eliminate all slashes by setting complete=true
	 *
	 * @param array The subject array
	 * @param int See php documentation about this
	 * @param string See php documentation about this
	 * @param string The fields which should be checked for, separated by spaces
	 * @param bool Select true to use stripslashes_complete instead of stripslashes
	 * @return array The array with stripslashe'd strings
	 * @author Florian Lippert <flo@redenswert.de>
	 */
	function stripslashes_array($subject, $fields = '', $complete = false)
	{
		if(is_array($subject))
		{
			if( !is_array( $fields ) )
			{
				$fields = array_trim(explode(' ', $fields));
			}

			foreach( $subject as $field => $value )
			{
				if( ( !is_array($fields) || empty($fields) ) || (is_array($fields) && !empty($fields) && in_array($field, $fields) ) )
				{
					/**
					 * Just call ourselve to manage multi-dimensional arrays
					 */
					$subject[$field] = stripslashes_array( $subject[$field], $fields, $complete );
				}
			}
		}
		else
		{
			if( $complete == true )
			{
				$subject = stripslashes_complete( $subject );
			}
			else
			{
				$subject = stripslashes( $subject );
			}
		}
		return $subject;
	}

	/**
	 * Returns if an emailaddress is in correct format or not
	 *
	 * @param string The email address to check
	 * @return bool Correct or not
	 * @author Florian Lippert <flo@redenswert.de>
	 */
	function verify_email($email)
	{
		$email=strtolower($email);

		return (bool)preg_match('/^([_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*(\.[a-zA-Z]{2,}))$/si',$email);
	}

	/**
	 * Returns whether a URL is in a correct format or not
	 *
	 * @param string URL to be tested
	 * @return bool
	 * @author Christian Hoffmann
	 */
	function verify_url($url)
	{
		return (bool)preg_match('!^https?://[a-z0-9\.\-/]*(?::\d{1,5})?(?:/[^\s\r\n\0]*)?$!i', $url);
	}

	/**
	 * Validates the given string by matching against the pattern, prints an error on failure and exits
	 *
	 * @param string $str the string to be tested (user input)
	 * @param string the $fieldname to be used in error messages
	 * @param string $pattern the regular expression to be used for testing
	 * @param string language id for the error
	 * @return string the clean string
	 */
	function validate($str, $fieldname, $pattern = '/^[^\r\n\0]*$/', $lng = 'stringformaterror')
	{
		if (preg_match($pattern, $str))
		{
			return $str;
		}
		standard_error($lng,$fieldname);
		exit;
	}

	/**
	 * Inserts a task into the PANEL_TASKS-Table
	 *
	 * @param int Type of task
	 * @param string Parameter 1
	 * @param string Parameter 2
	 * @param string Parameter 3
	 * @author Florian Lippert <flo@redenswert.de>
	 */
	function inserttask($type,$param1='',$param2='',$param3='')
	{
		global $db;

		if($type=='1')
		{
			$db->query(
				'DELETE FROM `' . TABLE_PANEL_TASKS . '` ' .
				'WHERE `type`="1"'
			);

			$db->query(
				'INSERT INTO `' . TABLE_PANEL_TASKS . '` ' .
				'(`type`) ' .
				'VALUES ' .
				'("1")'
			);
		}
		elseif($type=='2' && $param1!='' && $param2!='' && $param3!='')
		{
			$data=Array();
			$data['loginname']=$param1;
			$data['uid']=$param2;
			$data['gid']=$param3;
			$data=serialize($data);
			$db->query(
				'INSERT INTO `' . TABLE_PANEL_TASKS . '` ' .
				'(`type`, `data`) ' .
				'VALUES ' .
				'("2", "' . $db->escape($data) . '")'
			);
		}
		elseif($type=='3')
		{
			$db->query(
				'DELETE FROM `' . TABLE_PANEL_TASKS . '` ' .
				'WHERE `type`="3"'
			);

			$db->query(
				'INSERT INTO `' . TABLE_PANEL_TASKS . '` ' .
				'(`type`) ' .
				'VALUES ' .
				'("3")'
			);
		}
		elseif($type=='4')
		{
			$db->query(
				'DELETE FROM `' . TABLE_PANEL_TASKS . '` ' .
				'WHERE `type`="4"'
			);

			$db->query(
				'INSERT INTO `' . TABLE_PANEL_TASKS . '` ' .
				'(`type`) ' .
				'VALUES ' .
				'("4")'
			);
		}
	}

	/**
	 * Function which make webalizer statistics and returns used traffic of a month and year
	 *
	 * @param string Name of logfile
	 * @param string Place where stats should be build
	 * @param string Caption for webalizer output
	 * @param int Month
	 * @param int Year
	 * @return int Used traffic
	 * @author Florian Lippert <flo@redenswert.de>
	 */
	function webalizer_hist($logfile, $outputdir, $caption, $usersdomainlist, $month = 0, $year = 0)
	{
		global $settings;

		$httptraffic = 0;

		if ( file_exists ( $settings['system']['logfiles_directory'].$logfile.'-access.log' ) )
		{
			$yesterday = time()-(60*60*24);
			if($month == 0)
			{
				$month = date('n',$yesterday);
			}
			if($year == 0)
			{
				$year = date('Y',$yesterday);
			}

			$domainargs = '';
			foreach ( $usersdomainlist as $domainid => $domain )
			{
				$domainargs .= ' -r "'.escapeshellarg($domain).'"';
			}

			$outputdir = makeCorrectDir ($outputdir);
			if(!file_exists($outputdir))
			{
				safe_exec('mkdir -p "'.$outputdir.'"');
			}
			safe_exec('webalizer -o '.escapeshellarg($outputdir).' -n '.escapeshellarg($caption).$domainargs.
				' '.escapeshellarg($settings['system']['logfiles_directory'].$logfile.'-access.log'));

			$webalizer_hist=@file_get_contents($outputdir.'webalizer.hist');
			$webalizer_hist_rows=explode("\n",$webalizer_hist);
			while(list(,$webalizer_hist_row)=each($webalizer_hist_rows))
			{
				if($webalizer_hist_row != '')
				{
					/**
					 * Month: $webalizer_hist_row['0']
					 * Year:  $webalizer_hist_row['1']
					 * KB:    $webalizer_hist_row['5']
					 */
					$webalizer_hist_row=explode(' ',$webalizer_hist_row);
					if($webalizer_hist_row['0'] == $month && $webalizer_hist_row['1'] == $year)
					{
						$httptraffic = floatval($webalizer_hist_row['5']);
					}
				}
			}
		}
		return floatval($httptraffic);
	}

	/**
	 * Function which returns a secure path, means to remove all multiple dots and slashes
	 *
	 * @param string The path
	 * @return string The corrected path
	 * @author Florian Lippert <flo@redenswert.de>
	 */
	function makeSecurePath($path)
	{
		$search = Array ('#/+#', '#\.+#', '#\0+#');
		$replace = Array ('/', '.', '');
		$path = preg_replace($search, $replace, $path);

		return $path;
	}

	/**
	 * Function which returns a correct dirname, means to add slashes at the beginning and at the end if there weren't some
	 *
	 * @param string The dirname
	 * @return string The corrected dirname
	 * @author Florian Lippert <flo@redenswert.de>
	 */
	function makeCorrectDir($dir)
	{
		if(substr($dir, -1, 1) != '/')
		{
			$dir .= '/';
		}
		if(substr($dir, 0, 1) != '/')
		{
			$dir = '/'.$dir;
		}

		$dir = makeSecurePath ( $dir ) ;

		return $dir;
	}

	/**
	 * Function which returns a correct filename, means to add a slash at the beginning if there wasn't one
	 *
	 * @param string filename the filename
	 * @return string the corrected filename
	 * @author Florian Lippert <flo@redenswert.de>
	 * @author Michael Russ <mr@edvruss.com>
	 * @author Martin Burchert <eremit@adm1n.de>
	 */
	function makeCorrectFile($filename)
	{
		if ( substr($filename, 0, 1) != '/' )
		{
			$filename = '/'.$filename;
		}

		$filename = makeSecurePath ( $filename ) ;

		return $filename;
	}

	/**
	 * Function which returns a correct destination for Postfix Virtual Table
	 *
	 * @param string The destinations
	 * @return string the corrected destinations
	 * @author Florian Lippert <flo@redenswert.de>
	 */
	function makeCorrectDestination($destination)
	{
		$search   = '/ +/';
		$replace  = ' ';
		$destination = preg_replace($search, $replace, $destination);

		if ( substr($destination, 0, 1) == ' ' )
		{
			$destination = substr ( $destination , 1 ) ;
		}
		if ( substr($destination, -1, 1) == ' ' )
		{
			$destination = substr ( $destination , 0 , strlen ( $destination ) - 1 ) ;
		}

		return $destination;
	}

	/**
	 * Function which updates all counters of used ressources in panel_admins and panel_customers
	 *
	 * @author Florian Lippert <flo@redenswert.de>
	 */
	function updateCounters ()
	{
		global $db;
		$admin_resources = Array () ;

		// Customers
		$customers = $db->query(
			'SELECT `customerid`, `adminid`, ' .
			' `diskspace`, `traffic_used`, `mysqls`, `ftps`, `emails`, `email_accounts`, `email_forwarders`, `subdomains` ' .
			'FROM `' . TABLE_PANEL_CUSTOMERS . '` ' .
			'ORDER BY `customerid`'
		);

		while($customer = $db->fetch_array($customers))
		{
			if ( ! isset ( $admin_resources[$customer['adminid']] ) )
			{
				$admin_resources[$customer['adminid']] = Array () ;
			}

			if ( ! isset ( $admin_resources[$customer['adminid']]['diskspace_used'] ) )
			{
				$admin_resources[$customer['adminid']]['diskspace_used'] = 0 ;
			}
			if ( ($customer['diskspace']/1024) != '-1' )
			{
				$admin_resources[$customer['adminid']]['diskspace_used'] += intval_ressource ( $customer['diskspace'] ) ;
			}

			if ( ! isset ( $admin_resources[$customer['adminid']]['traffic_used'] ) )
			{
				$admin_resources[$customer['adminid']]['traffic_used'] = 0 ;
			}
			$admin_resources[$customer['adminid']]['traffic_used'] += $customer['traffic_used'] ;

			if ( ! isset ( $admin_resources[$customer['adminid']]['mysqls_used'] ) )
			{
				$admin_resources[$customer['adminid']]['mysqls_used'] = 0 ;
			}
			if ( $customer['mysqls'] != '-1' )
			{
				$admin_resources[$customer['adminid']]['mysqls_used'] += intval_ressource ( $customer['mysqls'] ) ;
			}

			if ( ! isset ( $admin_resources[$customer['adminid']]['ftps_used'] ) )
			{
				$admin_resources[$customer['adminid']]['ftps_used'] = 0 ;
			}
			if ( $customer['ftps'] != '-1' )
			{
				$admin_resources[$customer['adminid']]['ftps_used'] += intval_ressource ( $customer['ftps'] ) ;
			}

			if ( ! isset ( $admin_resources[$customer['adminid']]['emails_used'] ) )
			{
				$admin_resources[$customer['adminid']]['emails_used'] = 0 ;
			}
			if ( $customer['emails'] != '-1' )
			{
				$admin_resources[$customer['adminid']]['emails_used'] += intval_ressource ( $customer['emails'] ) ;
			}

			if ( ! isset ( $admin_resources[$customer['adminid']]['email_accounts_used'] ) )
			{
				$admin_resources[$customer['adminid']]['email_accounts_used'] = 0 ;
			}
			if ( $customer['email_accounts'] != '-1' )
			{
				$admin_resources[$customer['adminid']]['email_accounts_used'] += intval_ressource ( $customer['email_accounts'] ) ;
			}

			if ( ! isset ( $admin_resources[$customer['adminid']]['email_forwarders_used'] ) )
			{
				$admin_resources[$customer['adminid']]['email_forwarders_used'] = 0 ;
			}
			if ( $customer['email_forwarders'] != '-1' )
			{
				$admin_resources[$customer['adminid']]['email_forwarders_used'] += intval_ressource ( $customer['email_forwarders'] ) ;
			}

			if ( ! isset ( $admin_resources[$customer['adminid']]['subdomains_used'] ) )
			{
				$admin_resources[$customer['adminid']]['subdomains_used'] = 0 ;
			}
			if ( $customer['subdomains'] != '-1' )
			{
				$admin_resources[$customer['adminid']]['subdomains_used'] += intval_ressource ( $customer['subdomains'] ) ;
			}

			$customer_mysqls = $db->query_first(
				'SELECT COUNT(*) AS `number_mysqls` ' .
				'FROM `'.TABLE_PANEL_DATABASES.'` ' .
				'WHERE `customerid` = "'.(int)$customer['customerid'].'"'
			);

			$customer_emails = $db->query_first(
				'SELECT COUNT(*) AS `number_emails` ' .
				'FROM `'.TABLE_MAIL_VIRTUAL.'` ' .
				'WHERE `customerid` = "'.(int)$customer['customerid'].'"'
			);

			$customer_emails_result = $db->query(
				'SELECT `email`, `email_full`, `destination`, `popaccountid` AS `number_email_forwarders` ' .
				'FROM `'.TABLE_MAIL_VIRTUAL.'` ' .
				'WHERE `customerid` = "'.(int)$customer['customerid'].'" '
			);

			$customer_email_forwarders = 0;
			$customer_email_accounts = 0;
			while ( $customer_emails_row = $db->fetch_array ( $customer_emails_result ) )
			{
				if ( $customer_emails_row['destination'] != '' )
				{
					$customer_emails_row['destination'] = explode ( ' ' , makeCorrectDestination ( $customer_emails_row['destination'] ) ) ;
					$customer_email_forwarders += count ( $customer_emails_row['destination'] ) ;
					if ( in_array ( $customer_emails_row['email_full'] , $customer_emails_row['destination'] ) )
					{
						$customer_email_forwarders -= 1 ;
						$customer_email_accounts ++ ;
					}
				}
			}

			$customer_ftps = $db->query_first(
				'SELECT COUNT(*) AS `number_ftps` ' .
				'FROM `'.TABLE_FTP_USERS.'` ' .
				'WHERE `customerid` = "'.(int)$customer['customerid'].'"'
			);

			$customer_subdomains = $db->query_first(
				'SELECT COUNT(*) AS `number_subdomains` ' .
				'FROM `'.TABLE_PANEL_DOMAINS.'` ' .
				'WHERE `customerid` = "'.(int)$customer['customerid'].'" ' .
				'AND `parentdomainid` <> "0"'
			);

			$db->query(
				'UPDATE `'.TABLE_PANEL_CUSTOMERS.'` ' .
				'SET `mysqls_used` = "'.(int)$customer_mysqls['number_mysqls'].'", ' .
				'    `emails_used` = "'.(int)$customer_emails['number_emails'].'", ' .
				'    `email_accounts_used` = "'.(int)$customer_email_accounts.'", ' .
				'    `email_forwarders_used` = "'.(int)$customer_email_forwarders.'", ' .
				'    `ftps_used` = "'.(int)($customer_ftps['number_ftps']-1).'", ' .
				'    `subdomains_used` = "'.(int)$customer_subdomains['number_subdomains'].'" ' .
				'WHERE `customerid` = "'.(int)$customer['customerid'].'"'
			);
		}

		// Admins
		$admins = $db->query(
			'SELECT `adminid` ' .
			'FROM `'.TABLE_PANEL_ADMINS.'` ' .
			'ORDER BY `adminid`'
		);

		while($admin = $db->fetch_array($admins))
		{
			$admin_customers = $db->query_first(
				'SELECT COUNT(*) AS `number_customers` ' .
				'FROM `'.TABLE_PANEL_CUSTOMERS.'` ' .
				'WHERE `adminid` = "'.(int)$admin['adminid'].'"'
			);

			$admin_domains = $db->query_first(
				'SELECT COUNT(*) AS `number_domains` ' .
				'FROM `'.TABLE_PANEL_DOMAINS.'` ' .
				'WHERE `adminid` = "'.(int)$admin['adminid'].'" ' .
				'AND `isemaildomain` = "1"'
			);

			if ( ! isset ( $admin_resources[$admin['adminid']] ) )
			{
				$admin_resources[$admin['adminid']] = Array () ;
			}
			if ( ! isset ( $admin_resources[$admin['adminid']]['diskspace_used'] ) )
			{
				$admin_resources[$admin['adminid']]['diskspace_used'] = 0 ;
			}
			if ( ! isset ( $admin_resources[$admin['adminid']]['traffic_used'] ) )
			{
				$admin_resources[$admin['adminid']]['traffic_used'] = 0 ;
			}
			if ( ! isset ( $admin_resources[$admin['adminid']]['mysqls_used'] ) )
			{
				$admin_resources[$admin['adminid']]['mysqls_used'] = 0 ;
			}
			if ( ! isset ( $admin_resources[$admin['adminid']]['ftps_used'] ) )
			{
				$admin_resources[$admin['adminid']]['ftps_used'] = 0 ;
			}
			if ( ! isset ( $admin_resources[$admin['adminid']]['emails_used'] ) )
			{
				$admin_resources[$admin['adminid']]['emails_used'] = 0 ;
			}
			if ( ! isset ( $admin_resources[$admin['adminid']]['email_accounts_used'] ) )
			{
				$admin_resources[$admin['adminid']]['email_accounts_used'] = 0 ;
			}
			if ( ! isset ( $admin_resources[$admin['adminid']]['email_forwarders_used'] ) )
			{
				$admin_resources[$admin['adminid']]['email_forwarders_used'] = 0 ;
			}
			if ( ! isset ( $admin_resources[$admin['adminid']]['subdomains_used'] ) )
			{
				$admin_resources[$admin['adminid']]['subdomains_used'] = 0 ;
			}

			$db->query(
				'UPDATE `'.TABLE_PANEL_ADMINS.'` ' .
				'SET `customers_used` = "'.(int)$admin_customers['number_customers'].'", ' .
				'    `diskspace_used` = "'.(int)$admin_resources[$admin['adminid']]['diskspace_used'].'", ' .
				'    `mysqls_used` = "'.(int)$admin_resources[$admin['adminid']]['mysqls_used'].'", ' .
				'    `emails_used` = "'.(int)$admin_resources[$admin['adminid']]['emails_used'].'", ' .
				'    `email_accounts_used` = "'.(int)$admin_resources[$admin['adminid']]['email_accounts_used'].'", ' .
				'    `email_forwarders_used` = "'.(int)$admin_resources[$admin['adminid']]['email_forwarders_used'].'", ' .
				'    `ftps_used` = "'.(int)$admin_resources[$admin['adminid']]['ftps_used'].'", ' .
				'    `subdomains_used` = "'.(int)$admin_resources[$admin['adminid']]['subdomains_used'].'", ' .
				'    `traffic_used` = "'.(int)$admin_resources[$admin['adminid']]['traffic_used'].'", ' .
				'    `domains_used` = "'.(int)$admin_domains['number_domains'].'" ' .
				'WHERE `adminid` = "'.(int)$admin['adminid'].'"'
			);
		}
	}

	/******************************************************
	 * Wrapper around the exec command.
	 *
	 * @author Martin Burchert <eremit@adm1n.de>
	 * @version 1.2
	 * @param string exec_string String to be executed
	 * @return string The result of the exec()
	 *
	 * History:
	 ******************************************************
	 * 1.0 : Initial Version
	 * 1.1 : Added |,&,>,<,`,*,$,~,? as security breaks.
	 * 1.2 : Removed * as security break
	 ******************************************************/
	function safe_exec($exec_string)
	{
		global $settings;
		//
		// define allowed system commands
		//
		$allowed_commands = array(
			'touch', 'chown', 'mkdir', 'webalizer', 'cp', 'du', 'chmod',
			$settings['system']['apachereload_command'],
			$settings['system']['bindreload_command']);
		//
		// check for ; in execute command
		//
		if ((stristr($exec_string,';')) or
			(stristr($exec_string,'|')) or
			(stristr($exec_string,'&')) or
			(stristr($exec_string,'>')) or
			(stristr($exec_string,'<')) or
			(stristr($exec_string,'`')) or
			(stristr($exec_string,'$')) or
			(stristr($exec_string,'~')) or
			(stristr($exec_string,'?')) )
		{
			die ('SECURITY CHECK FAILED!'."\n".'The execute string "'.htmlspecialchars($exec_string).
				'" is a possible security risk!'."\n".'Please check your whole server for security problems by hand!'."\n");
		}
		//
		// check if command is allowed here
		//
		$ok = false;
		foreach ($allowed_commands as $allowed_command)
		{
			if (strpos($exec_string, $allowed_command) == 0 && (strlen($exec_string) === ($allowed_command_pos = strlen($allowed_command)) || substr($exec_string, $allowed_command_pos, 1) === ' '))
			{
				$ok = true;
			}
		}
		if (!$ok)
		{
			die('SECURITY CHECK FAILED!'."\n".'Your command "'.htmlspecialchars($exec_string).'" is not allowed!'.
				"\n".'Please check your whole server for security problems by hand!'."\n");
		}
		//
		// execute the command and return output
		//

		// --- martin @ 08.08.2005 -------------------------------------------------------
		// fixing usage of uninitialised variable
		$return = '';
		// -------------------------------------------------------------------------------
		exec($exec_string, $return);
		return $return;
	}

	/******************************************************
	 * Navigation generator
	 *
	 * @author Martin Burchert <eremit@adm1n.de>
	 * @version 1.0
	 * @param string s The session-id of the user
	 * @param array userinfo the userinfo of the user
	 * @return string the content of the navigation bar
	 *
	 * History:
	 ******************************************************
	 * 1.0 : Initial Version
	 * 1.1 : Added new_window and required_resources (flo)
	 ******************************************************/
	function getNavigation($s, $userinfo)
	{
		global $db, $lng;

		$return = '';
		//
		// query database
		//
		$query  =
			'SELECT * ' .
			'FROM `'.TABLE_PANEL_NAVIGATION.'` ' .
			'WHERE `area`=\''.$db->escape(AREA).'\' AND (`parent_url`=\'\' OR `parent_url`=\' \') ' .
			'ORDER BY `order`, `id` ASC' ;
		$result = $db->query($query);
		//
		// presort in multidimensional array
		//
		while ($row = $db->fetch_array($result))
		{
			if ( $row['required_resources'] == '' || $userinfo[$row['required_resources']] > 0 || $userinfo[$row['required_resources']] == '-1' )
			{
				$row['parent_url'] = $row['url'] ;
				$row['isparent'] = 1;

				$nav[$row['parent_url']][] = _createNavigationEntry($s,$row);

				$subQuery =
					'SELECT * '.
					'FROM `'.TABLE_PANEL_NAVIGATION.'` '.
					'WHERE `area`=\''.$db->escape(AREA).'\' AND `parent_url`=\''.$db->escape($row['url']).'\' ' .
					'ORDER BY `order`, `id` ASC' ;
				$subResult = $db->query($subQuery);
				while($subRow = $db->fetch_array($subResult))
				{
					if ( $subRow['required_resources'] == '' || $userinfo[$subRow['required_resources']] > 0 || $userinfo[$subRow['required_resources']] == '-1' )
					{
						$subRow['isparent'] = 0;
						$nav[$row['parent_url']][] = _createNavigationEntry($s,$subRow);
					}
				}
			}
		}
		//
		// generate output
		//
		if ( (isset($nav)) && (sizeof($nav) > 0))
		{
			foreach ($nav as $parent_url => $row)
			{
				$navigation_links = '';
				foreach ($row as $id => $navElem )
				{
					if ($navElem['isparent'] == 1 )
					{
						$completeLink_ElementTitle = $navElem['completeLink'];
					}
					else
					{
						// assign url
						$completeLink = $navElem['completeLink'];
						// read template
						eval("\$navigation_links .= \"".getTemplate("navigation_link",1)."\";");
					}
				}
				if ( $navigation_links != '' )
				{
					eval("\$return .= \"".getTemplate("navigation_element",1)."\";");
				}
			}
		}
		return $return;
	}

	/**
	 * Processes a navigation entry in the database. It generates the correct
	 * link and language.
	 *
	 * @param string The sessionid.
	 * @param array The data recieved during the mysql query.
	 * @return array The processed data.
	 */
	function _createNavigationEntry($s, $data)
	{
		global $db, $lng;

		// get corect lang string
		$lngArr = explode ( ';' , $data['lang'] ) ;
		$data['text'] = $lng;
		foreach ($lngArr as $lKey => $lValue)
		{
			$data['text'] = $data['text'][$lValue] ;
		}
		if ( str_replace( ' ' , '' , $data['url'] ) != '' && !stristr($data['url'], 'nourl' ))
		{
			// append sid only to local
			if ( !preg_match('/^https?\:\/\//', $data['url'] ) && ( isset($s) && $s != '' ) )
			{
				// generate sid with ? oder &
				if ( strpos($data['url'], '?') !== false )
				{
					$data['url'] .= '&s='.$s;
				}
				else
				{
					$data['url'] .= '?s='.$s;
				}
			}
			$target = '';
			if ( $data['new_window'] == '1' )
			{
				$target = ' target="_blank"';
			}
			$data['completeLink'] = '<a href="' . htmlspecialchars($data['url']) . '"' . $target . ' class="menu">' . $data['text'] . '</a>' ;
		}
		else
		{
			$data['completeLink'] = $data['text'];
		}

		return $data;
	}

	/**
	 * Returns if an username is in correct format or not.
	 * A username is valid if it would be a username that is accepted by the
	 * useradd command.
	 *
	 * @param string The username to check
	 * @return bool Correct or not
	 * @author Michael Duergner <michael@duergner.com>
	 */
	function check_username($username) {
		return preg_match('/^[a-zA-Z0-9][a-zA-Z0-9\-_]+\$?$/',$username);
	}

	/**
	 * Returns if an username_prefix is in correct format or not.
	 * A username_prefix is valid if the resulting username would be a username
	 * that is accepted by the useradd command.
	 *
	 * @param string The username to check
	 * @return bool Correct or not
	 * @author Michael Duergner <michael@duergner.com>
	 */
	function check_username_prefix($username_prefix) {
		return preg_match("/^[a-zA-Z0-9][a-zA-Z0-9\-\_]*$/",$username_prefix);
	}

	/**
	 * Returns if a mysql_prefix is in correct format or not.
	 *
	 * @param string The mysql_prefix to check
	 * @return bool Correct or not
	 * @author Michael Duergner <michael@duergner.com>
	 */
	function check_mysql_prefix($mysql_prefix) {
		return preg_match("/^[a-zA-Z0-9\-\_]+$/",$mysql_prefix);
	}

	/**
	 * Returns an integer of the given value which isn't negative.
	 * Returns -1 if the given value was -1.
	 *
	 * @param any The value
	 * @return int The positive value
	 * @author Florian Lippert <flo@redenswert.de>
	 */
	function intval_ressource ( $the_value )
	{
		$the_value = intval ( $the_value ) ;
		if ( $the_value < 0 && $the_value != '-1')
		{
			$the_value *= -1 ;
		}
		return $the_value ;
	}

	/**
	 * Returns a double of the given value which isn't negative.
	 * Returns -1 if the given value was -1.
	 *
	 * @param any The value
	 * @return double The positive value
	 * @author Florian Lippert <flo@redenswert.de>
	 */
	function doubleval_ressource ( $the_value )
	{
		$the_value = doubleval ( $the_value ) ;
		if ( $the_value < 0 && $the_value != '-1')
		{
			$the_value *= -1 ;
		}
		return $the_value ;
	}

	/**
	 * Replaces all occurences of variables defined in the second argument
	 * in the first argument with their values.
	 *
	 * @param string The string that should be searched for variables
	 * @param array The array containing the variables with their values
	 * @return string The submitted string with the variables replaced.
	 * @author Michael Duergner
	 */
	function replace_variables($text,$vars) {
		$pattern = "/\{([a-zA-Z0-9\-_]+)\}/";
		// --- martin @ 08.08.2005 -------------------------------------------------------
		// fixing usage of uninitialised variable
		$matches = array();
		// -------------------------------------------------------------------------------
		if(count($vars) > 0 && preg_match_all($pattern,$text,$matches)) {
			for($i = 0;$i < count($matches[1]);$i++) {
				$current = $matches[1][$i];
				if (isset ($vars[$current]) )
				{
					$var = $vars[$current];
					$text = str_replace("{" . $current . "}",$var,$text);
				}
			}
		}
		$text = str_replace ( '\n', "\n" , $text ) ;
		return $text;
	}

	/**
	 * Was a wrapper, now only for compatibility there
	 *
	 * @param string The string in which the html entities should be decoded.
	 * @return string The decoded string
	 * @author Michael Duergner
	 */
	function _html_entity_decode($string)
	{
		return html_entity_decode($string);
	}

	/**
	 * Calls html_entity_decode in a loop until the result doesn't differ from original anymore
	 *
	 * @param string The string in which the html entities should be eliminated.
	 * @return string The cleaned string
	 * @author Florian Lippert <flo@syscp.org>
	 */
	function html_entity_decode_complete( $string )
	{
		while( $string != _html_entity_decode( $string ) )
		{
			$string = _html_entity_decode( $string );
		}
		return $string;
	}

	/**
	 * Calls stripslashes in a loop until the result doesn't differ from original anymore
	 *
	 * @param string The string in which the slashes should be eliminated.
	 * @return string The cleaned string
	 * @author Florian Lippert <flo@syscp.org>
	 */
	function stripslashes_complete( $string )
	{
		while( $string != stripslashes( $string ) )
		{
			$string = stripslashes( $string );
		}
		return $string;
	}

	/**
	 * Check if the submitted string is a valid domainname, i.e.
	 * it consists only of the following characters ([a-z0-9][a-z0-9\-]+\.)+[a-z]{2,4}
	 *
	 * @param string The domainname which should be checked.
	 * @return boolean True if the domain is valid, false otherwise
	 * @author Michael Duergner
	 */
	function check_domain($domainname)
	{
		return preg_match('/^([a-z0-9][a-z0-9\-]+\.)+[a-z]{2,4}$/i',$domainname);
	}

	/**
	 * Returns an array of found directories
	 *
	 * This function checks every found directory if they match either $uid or $gid, if they do
	 * the found directory is valid. It uses recursive function calls to find subdirectories. Due
	 * to the recursive behauviour this function may consume much memory.
	 *
	 * @param  string   path       The path to start searching in
	 * @param  integer  uid        The uid which must match the found directories
	 * @param  integer  gid        The gid which must match the found direcotries
	 * @param  array    _fileList  recursive transport array !for internal use only!
	 * @return array    Array of found valid pathes
	 *
	 * @author Martin Burchert  <martin.burchert@syscp.de>
	 * @author Manuel Bernhardt <manuel.bernhardt@syscp.de>
	 */
	function findDirs ( $path, $uid, $gid, $_fileList = array() )
	{
		$dh = opendir( $path );
		while ( false !== ( $file = readdir( $dh ) ) )
		{
			if ( $file == '.' && (    fileowner( $path.'/'.$file ) == $uid
			                       || filegroup( $path.'/'.$file ) == $gid
			                     )
			   )
			{
				$_fileList[] = $path.'/';
			}
			if ( $file != '..' && $file != '.' && is_dir( $path.'/'.$file ) && is_readable( $path.'/'.$file ) )
			{
				$_fileList = findDirs( $path.'/'.$file, $uid, $gid, $_fileList );
			}
		}
		closedir( $dh );
		return $_fileList;
	}

	/**
	 * Returns a valid html tag for the choosen $fieldType for pathes
	 *
	 * @param  string   path       The path to start searching in
	 * @param  integer  uid        The uid which must match the found directories
	 * @param  integer  gid        The gid which must match the found direcotries
	 * @param  string   fieldType  Either "Manual" or "Dropdown"
	 * @return string   The html tag for the choosen $fieldType
	 *
	 * @author Martin Burchert  <martin.burchert@syscp.de>
	 * @author Manuel Bernhardt <manuel.bernhardt@syscp.de>
	 */
	function makePathfield( $path, $uid, $gid, $fieldType, $value='' )
	{
		global $lng;

		$value = str_replace( $path, '', $value );
		$field = '';
		if ( $fieldType == 'Manual' )
		{
			$field = '<input type="text" name="path" value="'.htmlspecialchars($value).'" size="30" />';
		}
		elseif ( $fieldType == 'Dropdown' )
		{
			$dirList = findDirs( $path, $uid, $gid );
			if ( sizeof( $dirList ) > 0 )
			{
				$field   = '<select name="path">';
				foreach ( $dirList as $key => $dir )
				{
					if( strpos( $dir, $path ) === 0 )
					{
						$dir = substr( $dir, strlen( $path ) );
					}
					$field .= makeoption( $dir, $dir, $value );
				}
				$field .= '</select>';
			}
			else
			{
				$field = $lng['panel']['dirsmissing'];
				$field .= '<input type="hidden" name="path" value="/" />';
			}
		}
		return $field;
	}

?>
