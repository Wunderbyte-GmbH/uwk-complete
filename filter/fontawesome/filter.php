<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Filter converting defined FontAwesome icons in brackets in to HTML embed code.
 *
 * @package    filter_fontawesome
 * @copyright  2013 Julian Ridden <julian@moodleman.net>
 * @author     2019 onwards Adrian Perez, Fernfachhochschule Schweiz (FFHS) <adrian.perez@ffhs.ch>
 * @author     2022 onwards Sascha Vogel, Fernfachhochschule Schweiz (FFHS) <sascha.vogel@ffhs.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// For backwards compatibility with Moodle 4.4 and below.
class_alias(\filter_fontawesome\text_filter::class, \filter_fontawesome::class);
