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
 * quizaccess_usernumattempts data generator.
 *
 * @package     quizaccess_usernumattempts
 * @category    quizaccess
 * @subpackage  test
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * quizaccess_usernumattempts data generator class.
 */
class quizaccess_usernumattempts_generator extends component_generator_base {

    public function set_user_credits($userid, $quizid, $credits) {
        global $DB;

        if ($oldrec = $DB->get_record('qa_usernumattempts_limits', array('userid' => $userid, 'quizid' => $quizid))) {
            $oldrec->maxattempts = $credits;
            $DB->update_record('qa_usernumattempts_limits', $oldrec);
        } else {
            $rec = new Stdclass();
            $rec->userid = $userid;
            $rec->quizid = $quizid;
            $rec->maxattempts = $credits;
            $DB->insert_record('qa_usernumattempts_limits', $rec);
        }
    }

}

