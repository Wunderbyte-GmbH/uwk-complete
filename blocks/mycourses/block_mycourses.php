<?PHP

require_once($CFG->dirroot.'/blocks/mycourses/HTML_TreeMenu-1.2.0/TreeMenu.php');
require_once($CFG->dirroot.'/blocks/mycourses/deprecatedlib.php'); // added by G. Schwed to include local and adapted library with old (deprecated) functions from Moodle 2.7

class block_mycourses extends block_base {
    const DEBUG = false;
    
    const DEFAULT_MYCACT    = false;
    const DEFAULT_MYCINACT  = false;
    const DEFAULT_MYCMEM    = true;
    
    const DEFAULT_MYCACT_PRIV    = true;
    const DEFAULT_MYCINACT_PRIV  = true;
    const DEFAULT_MYCMEM_PRIV    = true;    
    
    protected $default_mycact;
    protected $default_mycinact;
    protected $default_mycmem;    
    
    protected $mycact;
    protected $mycinact;
    protected $mycmem;
    
    protected $admin;
    protected $usemarkers;
    protected $showmarkers;
            
	function init() {
		$this->title        = get_string('mycourses','block_mycourses');
		$this->version      = 2007032202;
                $this->admin        = false;
                $this->usemarkers   = false;
                $this->showmarkers  = false;
                
                $this->default_mycact   = self::DEFAULT_MYCACT;
                $this->default_mycinact = self::DEFAULT_MYCINACT;
                $this->default_mycmem   = self::DEFAULT_MYCMEM;
	}

	function has_config() {
		return false;
	}

	function specialization() {
		global $COURSE,$USER;

		$coursecontext = context_course::instance($COURSE->id); // mod. by G. Schwed, 2015-10-22
		// if user is teacher, editingteacher, coursecreator
		if (user_has_role_assignment($USER->id, 1) || 
                    user_has_role_assignment($USER->id, 2) || 
//                  user_has_role_assignment($USER->id, 3) || 
//                  user_has_role_assignment($USER->id, 4) || 
                    user_has_role_assignment($USER->id, 50)) 
		{
			$this->admin        = false;
                        $this->usemarkers   = true;
                        $this->showmarkers  = true;
                        
                        $this->default_mycact   = self::DEFAULT_MYCACT_PRIV;
                        $this->default_mycinact = self::DEFAULT_MYCINACT_PRIV;
                        $this->default_mycmem   = self::DEFAULT_MYCMEM_PRIV;
		}
		if(has_capability('moodle/site:config', $coursecontext)) {
			$this->admin        = true;
                        $this->usemarkers   = true;
                        $this->showmarkers  = true;
                        
                        $this->default_mycact   = self::DEFAULT_MYCACT_PRIV;
                        $this->default_mycinact = self::DEFAULT_MYCINACT_PRIV;
                        $this->default_mycmem   = self::DEFAULT_MYCMEM_PRIV;
		}
                $this->set_marker_options();
	}

	function get_content() {
		global $USER, $CFG, $THEME, $SESSION, $PAGE;

		if($this->content !== NULL) {
			return $this->content;
		}

		//don't display the block for quoodle mobile version
		
		if($PAGE->theme->name == "mymobile" || $PAGE->theme->name == "quoodle") 
			return null;
		
                $this->content = new stdClass(); // changed by G. Schwed; original: '... = New object;'
		$this->content->text = $this->get_header();

                $footer = '';
                if( self::DEBUG ) {
                    $start  = microtime(true);
                }
                
		$this->content->text .= $this->build_menu();
                
                if( self::DEBUG ) {
                    $end    = microtime(true);
                    $dur    = $end - $start;
                    $footer = 'Menu-Rendertime: '. $dur. ' sec';
                }
                
		$this->content->footer = $footer;

		return $this->content;
	}
/*
	function set_admin_options() {
		global $SESSION;

		if (isset($_GET['mycact'])) {
			$SESSION->mycact = ($_GET['mycact'] == 'on') ? true : false;
		} else if (!isset($SESSION->mycact)) {
			$SESSION->mycact = true;
		}

		if (isset($_GET['mycinact'])) {
			$SESSION->mycinact = ($_GET['mycinact'] == 'on') ? true : false;
		} else if (!isset($SESSION->mycinact)) {
			$SESSION->mycinact = true;
		}

		$this->mycact = $SESSION->mycact;
		$this->mycinact = $SESSION->mycinact;
	}
*/
	function set_marker_options() {
		global $SESSION;

		$this->mycmem   = $this->default_mycmem;
		$this->mycinact = $this->default_mycinact;
		$this->mycact   = $this->default_mycact;

                if( !$this->usemarkers ) {
                    return;
                }
                
		if (isset($_GET['mycmem'])) {
			$SESSION->mycmem = ($_GET['mycmem'] == 'on') ? true : false;
		} else if (!isset($SESSION->mycmem)) {
			$SESSION->mycmem = $this->default_mycmem;
		}

		if (isset($_GET['mycinact'])) {
			$SESSION->mycinact = ($_GET['mycinact'] == 'on') ? true : false;
		} else if (!isset($SESSION->mycinact)) {
			$SESSION->mycinact = $this->default_mycinact;
		}

		if (isset($_GET['mycact'])) {
			$SESSION->mycact = ($_GET['mycact'] == 'on') ? true : false;
		} else if (!isset($SESSION->mycact)) {
			$SESSION->mycact = $this->default_mycact;
		}

		$this->mycmem   = $SESSION->mycmem;
		$this->mycinact = $SESSION->mycinact;
		$this->mycact   = $SESSION->mycact;
	}

	function get_header() {
		global $CFG, $FULLME, $SESSION;

                $html = '';
                
		#if (!$this->admin ||
		#		/// This is needed in order for this block to work with custom title hack.
		#		/// Return just the title (no filters) if we are editing the title.
		#		(($this->edit_controls !== null) &&
		#				($SESSION->block_title_edit[$this->instance->id] === true))) {
		#	return $this->title;
		#}

		$url = preg_replace('/([\&\?]mycact=(on|off)|[\&\?]mycinact=(on|off)|[\&\?]mycmem=(on|off))/', '', $FULLME);
		$aurl = $url;
		$murl = $url;

		$img = '';
		$aimg = '';
		$mimg = '';

//		if ($this->admin) {
                if($this->showmarkers) {
			if ($this->mycinact) {
				$img = $CFG->wwwroot.'/blocks/mycourses/pix/inacton.gif';
				$title = get_string('hideinactivecourses','block_mycourses');
				if (strpos($url, '?')) {
					$url .= '&mycinact=off';
				} else {
					$url .= '?mycinact=off';
				}
			} else {
				$img = $CFG->wwwroot.'/blocks/mycourses/pix/inactoff.gif';
				$title = get_string('showinactivecourses','block_mycourses');
				if (strpos($url, '?')) {
					$url .= '&mycinact=on';
				} else {
					$url .= '?mycinact=on';
				}
			}

			if ($this->mycact) {
				$aimg = $CFG->wwwroot.'/blocks/mycourses/pix/acton.gif';
				$atitle = get_string('hideactivecourses','block_mycourses');
				if (strpos($aurl, '?')) {
					$aurl .= '&mycact=off';
				} else {
					$aurl .= '?mycact=off';
				}
			} else {
				$aimg = $CFG->wwwroot.'/blocks/mycourses/pix/actoff.gif';
				$atitle = get_string('showactivecourses','block_mycourses');
				if (strpos($aurl, '?')) {
					$aurl .= '&mycact=on';
				} else {
					$aurl .= '?mycact=on';
				}
			}

			if ($this->mycmem) {
				$mimg = $CFG->wwwroot.'/blocks/mycourses/pix/memon.gif';
				$mtitle = get_string('hidemycourses','block_mycourses');
				if (strpos($murl, '?')) {
					$murl .= '&mycmem=off';
				} else {
					$murl .= '?mycmem=off';
				}
			} else {
				$mimg = $CFG->wwwroot.'/blocks/mycourses/pix/memoff.gif';
				$mtitle = get_string('showmycourses','block_mycourses');
				if (strpos($murl, '?')) {
					$murl .= '&mycmem=on';
				} else {
					$murl .= '?mycmem=on';
				}
			}
                
                        $html = <<<EOLINKS
<div id="block_mycourses_markers">
  <a class="bmc_left"   href="{$url}"  title="{$title}" ><img src="{$img}"  alt="{$title}"  /></a>
  <a class="bmc_middle" href="{$aurl}" title="{$atitle}"><img src="{$aimg}" alt="{$atitle}" /></a>
  <a class="bmc_right"  href="{$murl}" title="{$mtitle}"><img src="{$mimg}" alt="{$mtitle}" /></a>
</div>
EOLINKS;

  		}
                
		return $html;
	}


	function build_menu() {
		global $CFG, $USER, $SITE, $COURSE, $DB;

		$this->menu = new HTML_TreeMenu();

		$adminseeall = true;
		if (isset($CFG->block_course_list_adminview)) {
			if ( $CFG->block_course_list_adminview == 'own'){
				$adminseeall = false;
			}
		}

		/// Build a tree of categories for later use.
		$categories = get_categories_old(0); // original depricated, now included in local file
                #$categories = coursecat::get(0)->get_children(); // new by G. Schwed
		$this->cattree = $this->load_cattree($categories);

		$coursecontext = context_course::instance($COURSE->id); // mod. by G. Schwed, 2015-10-22
		$courses = array();
                if ( isset($USER->id) ) {
                    $courses = enrol_get_my_courses();
                    $addcourses = get_user_capability_course('moodle/course:view', $USER->id, true, 'fullname, shortname, category, sortorder, visible');
                    if($addcourses) {
                      foreach( $addcourses as &$addcourse ) {
                        if( !isset($courses[$addcourse->id]) ) {
                          $addcourse->directlyenrolled = false;
                          $courses[$addcourse->id] = &$addcourse;
                        }
                      }
                    }
		}
/*
                echo "<pre>";
                print_r($courses);
                echo "</pre>";
*/
/*                
		if (isset($USER->id) && !(has_capability('moodle/site:config', $coursecontext) && $adminseeall)) {    // Just print My Courses
			// Schwed: following changes to show courses even for invisible managers
			// original $courses = enrol_get_my_courses(); // shows only where user is enrolled visible; disabled by G. Schwed
			//$courses = get_user_capability_course('moodle/course:viewparticipants', $USER->id, true, 'fullname, shortname, category, sortorder, visible'); // add by G. Schwed
                    // harald.bamberger@donau-uni.ac.at 20190423
                    //$courses = enrol_get_my_courses(null, null, 0, array(), true);
                    $courses = enrol_get_my_courses();
                    $addcourses = get_user_capability_course('moodle/course:view', $USER->id, true, 'fullname, shortname, category, sortorder, visible');
                    foreach( $addcourses as &$addcourse ) {
                      $courses[$addcourse->id] = &$addcourse;
                    }
		} else {
			//$courses = $DB->get_records('course'); //original
                    // harald.bamberger@donau-uni.ac.at 20190423
                    $courses = $DB->get_records('course', null, '', 'id, fullname, shortname, category, sortorder, visible');
                }
*/
		/// First, load all of the courses into an array of categories.
		foreach ($courses as $course) {
			if (!$course->category) {
				continue;
			}
			$this->has_courses($course);
		}

		$url = $CFG->wwwroot.'" title="'.$SITE->fullname;
		$text = ' '.$SITE->shortname;
		$cssclass = 'treeMenuDefault';
		$icon = 'home.gif';
		$expandedIcon = 'home.gif';

		$mnode = new HTML_TreeNode(array('text' => $text, 'link' => $url, 'icon' => $icon, 'cssClass' => $cssclass,
				'expandedIcon' => $expandedIcon, 'expanded' => true));

		$this->create_tree_menu($this->cattree, $mnode);
		$this->menu->addItem($mnode);
		$treeMenu = new HTML_TreeMenu_DHTML($this->menu, array('images' => $CFG->wwwroot.'/blocks/mycourses/HTML_TreeMenu-1.2.0/images',
				'defaultClass' => 'treeMenuDefault'));

		ob_start();
		$treeMenu->printMenu();
		$output = '
		    <style type="text/css">
		        .treeMenuDefault {
		    	font-size: 90%;
		    	font-style: normal;
		        }
		        .treeMenuBold {
		    	font-size: 90%;
		    	font-weight: bold;
		        }
		    </style>
		';
		$output .= '<script src="'.$CFG->wwwroot.'/blocks/mycourses/HTML_TreeMenu-1.2.0/TreeMenu.js" language="JavaScript" type="text/javascript"></script>';
		$output .= ob_get_contents();
		ob_end_clean();

		return $output;
	}


	function print_menu($branches, $output='', $indent=0) {
		foreach ($branches as $node) {
			if (!empty($output)) {
				$output .= '<br />';
			}
			if ($indent) {
				for ($i=0; $i<$indent; $i++) {
					$output .= '&nbsp;';
				}
			}
			$output .= $node->node->name;
			if ($node->branches) {
				$output = $this->print_menu($node->branches, $output, $indent+2);
			}
		}
		return $output;
	}


	function load_cattree($categories) {
		foreach ($categories as $category) {
			if ($cats = get_categories_old($category->id)) { // original, deprecated
			#if ($cats = coursecat::get($category->id)->get_children()) { // new by G. Schwed
				$categories[$category->id]->categories = $this->load_cattree($cats);
			} else {
				$categories[$category->id]->categories = array();
			}
			$categories[$category->id]->hascourses = false;
			$this->categories[(int)$category->id] = &$categories[$category->id];
		}
		return $categories;
	}


	/** The method text_fix(1) takes an introduction of the text, to show in the block
	 * uses a configuration variable with the number of chars to be shown in block for each note text
	 *
	 * @param string $text  : the note text
	 *
	 * @return string    */
	function text_fix($text) {
		global $CFG;

		$chars = (isset($CFG->block_mycourses_chars)) ? $CFG->block_mycourses_chars : 35;
		$points = (strlen($text) > $chars) ? '...' : '';
		return (substr($text, 0, $chars).$points);
	}

	/** Function that loads the courses applicable to the current selected filter settings.
	 *
	 * @param object $course The course to check against.  */
	function has_courses($course) {
		global $USER;
/*                
		/// Load this course if its not filtered.
		/// If its a visible course, and the active courses filter is off, don't show it.
		if ($course->visible && !$this->mycact && !(user_has_role_assignment($USER->id, 3,context_course::instance($course->id)->id))) { // mod. by  G. Schwed, 2015-10-22
			return;

			/// If its an inactive course, and the inactive course filter is off, don't show it.
		} else if (!$course->visible && !$this->mycinact) {
			return;

			/// If its a marked course, and the marked course filter is off and the course is active
			/// (inactivity takes precedence over marked), don't show it.
		} else if (!$this->mycmem && !$this->admin && $course->visible &&
				(user_has_role_assignment($USER->id, 3,context_course::instance($course->id)->id))) { // mod. by G. Schwed 2015-10-22
			return;
		}
 */

                if( $course->visible ) {
                    //if( is_enrolled(context_course::instance($course->id), $USER, '', true) ) {
                    if( isset($course->directlyenrolled) && $course->directlyenrolled === false ) {
                        // user is not directly enrolled in course but has permission from a higher level
                        $course->cssclass = 'bhactive';
                        if( !$this->mycact ) {
                            return;
                        }
                    } else {                        
                        // user is enrolled in course
                        $course->cssclass = 'bhenrolled';
                        if( !$this->mycmem ) {
                            return;
                        }
                    }
                } else {
                    $course->cssclass = 'bhinactive';
                    if( !$this->mycinact ) {
                        return;
                    }
                }
                
                // harald.bamberger@donau-uni.ac.at 20190413 init category if not already done - begin
		if( !isset($this->categories[$course->category]) ) {
                    $this->categories[$course->category] = new StdClass();
                    $this->categories[$course->category]->hascourses = false;
                    $this->categories[$course->category]->parent     = 0;
                    $this->categories[$course->category]->courses    = array();
                }
                // harald.bamberger@donau-uni.ac.at 20190413 init category if not already done - end

		$this->categories[$course->category]->hascourses = true;
		$this->categories[$course->category]->courses[$course->sortorder] = $course;

		/// Make sure the courses are in the order specified.
		ksort($this->categories[$course->category]->courses);

		$catid = $course->category;
		while ($this->categories[$catid]->parent > 0) {
			$catid = $this->categories[$catid]->parent;
			$this->categories[$catid]->hascourses = true;
		}
	}


	function create_tree_menu($categories, &$pnode) {
		global $CFG;

		$nicon         = 'folder.gif';
		$eicon = 'folder-expanded.gif';

		foreach ($categories as $catid => $catnode) {
			if (!$catnode->hascourses) continue;

                        // following line changed by G. Schwed, 2016-09-22
			#$node = &$pnode->addItem(new HTML_TreeNode(array('text' => ' '.$catnode->name, 'link' => '',
			$node = $pnode->addItem(new HTML_TreeNode(array('text' => ' '.$catnode->name, 'link' => '',
					'icon' => $nicon, 'expandedIcon' => $eicon,
					'cssClass' => 'treeMenuDefault')));

			if (!empty($catnode->categories)) {
				$this->create_tree_menu($catnode->categories, $node);
			}

			if (!empty($catnode->courses)) {
				foreach ($catnode->courses as $course) {
					$linkcss = '';
					$url = $CFG->wwwroot.'/course/view.php?id='.$course->id.
					'" title="'.htmlspecialchars($course->fullname, ENT_QUOTES).$linkcss;
					$text = $course->shortname;
                                        
                                        // harald.bamberger@donau-uni.ac.at 20190424
                                        $cssclasses = array('treeMenuDefault');
                                        if( isset($course->cssclass) ) {
                                            $cssclasses[] = $course->cssclass;
                                        }
					//$cssclass = 'treeMenuDefault'; // original
                                        $cssclass = implode(' ', $cssclasses);
					$node->addItem(new HTML_TreeNode(array('text' => $text, 'link' => $url,
							'cssClass' => $cssclass)));
				}
			}
		}
	}

	function applicable_formats() {
		// Default case: the block can be used in all course types
		return array('all' => true,
				'site' => true);
	}
}
?>
