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
 * Implementaton of the quizaccess_usernumattempts plugin.
 *
 * @package     quizaccess_usernumattempts
 * @category    quizaccess
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   2014 onwards Valery Fremaux (http://www.mylearningfactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/quiz/accessrule/accessrulebase.php');

/**
 * A rule controlling the number of attempts allowed per user.
 */
class quizaccess_usernumattempts extends quiz_access_rule_base {

    public static function make(quiz $quizobj, $timenow, $canignoretimelimits) {

        return new self($quizobj, $timenow);
    }

    public function description() {
        global $USER, $DB;

        if ($this->is_enabled()) {
            $params = array('quizid' => $this->quiz->id, 'userid' => $USER->id);
            $limits = 0 + $DB->get_field('qa_usernumattempts_limits', 'maxattempts', $params);
            return get_string('attemptsallowedn', 'quizaccess_usernumattempts', $limits);
        } else {
            return;
        }
    }

    public static function add_settings_form_fields(mod_quiz_mod_form $quizform, MoodlequickForm $mform) {

        $mform->addElement('checkbox', 'usernumattemptsenabled', get_string('enable', 'quizaccess_usernumattempts'));
    }

    public function prevent_new_attempt($numprevattempts, $lastattempt) {
        global $USER, $DB;

        if ($this->is_enabled()) {
            $params = array('quizid' => $this->quiz->id, 'userid' => $USER->id);
            $usermaxattempts = $DB->get_field('qa_usernumattempts_limits', 'maxattempts', $params);

            if ($numprevattempts >= $usermaxattempts) {
                return get_string('nomoreattempts', 'quizaccess_usernumattempts');
            }
        }
        return false;
    }

    public function is_finished($numprevattempts, $lastattempt) {
        global $DB, $USER;

        if ($this->is_enabled()) {
            $params = array('quizid' => $this->quiz->id, 'userid' => $USER->id);
            $usermaxattempts = $DB->get_field('qa_usernumattempts_limits', 'maxattempts', $params);
            return $numprevattempts >= $this->quiz->attempts;
        }
        return false;
    }

    /**
     * Save any submitted settings when the quiz settings form is submitted. This
     * is called from {@link quiz_after_add_or_update()} in lib.php.
     * @param object $quiz the data from the quiz form, including $quiz->id
     *      which is the id of the quiz being saved.
     */
    public static function save_settings($quiz) {
        global $DB;

        if (!empty($quiz->usernumattemptsenabled)) {
            if ($oldrecord = $DB->get_record('qa_usernumattempts', array('quizid' => $quiz->id))) {
                $oldrecord->enabled = 1;
                $DB->update_record('qa_usernumattempts', $oldrecord);
            } else {
                $record = new Stdclass;
                $record->enabled = 1;
                $record->quizid = $quiz->id;
                $DB->insert_record('qa_usernumattempts', $record);
            }
        } else {
            if ($oldrecord = $DB->get_record('qa_usernumattempts', array('quizid' => $quiz->id))) {
                $oldrecord->enabled = 0;
                $DB->update_record('qa_usernumattempts', $record);
            }
        }
    }

    /**
     * Delete any rule-specific settings when the quiz is deleted. This is called
     * from {@link quiz_delete_instance()} in lib.php.
     * @param object $quiz the data from the database, including $quiz->id
     *      which is the id of the quiz being deleted.
     * @since Moodle 2.7.1, 2.6.4, 2.5.7
     */
    public static function delete_settings($quiz) {
        global $DB;

        $DB->delete_records('qa_usernumattempts', array('quizid' => $quiz->id));
        $DB->delete_records('qa_usernumattempts_limits', array('quizid' => $quiz->id));
    }

    /**
     * Return the bits of SQL needed to load all the settings from all the access
     * plugins in one DB query. The easiest way to understand what you need to do
     * here is probalby to read the code of {@link quiz_access_manager::load_settings()}.
     *
     * If you have some settings that cannot be loaded in this way, then you can
     * use the {@link get_extra_settings()} method instead, but that has
     * performance implications.
     *
     * @param int $quizid the id of the quiz we are loading settings for. This
     *     can also be accessed as quiz.id in the SQL. (quiz is a table alisas for {quiz}.)
     * @return array with three elements:
     *     1. fields: any fields to add to the select list. These should be alised
     *        if neccessary so that the field name starts the name of the plugin.
     *     2. joins: any joins (should probably be LEFT JOINS) with other tables that
     *        are needed.
     *     3. params: array of placeholder values that are needed by the SQL. You must
     *        used named placeholders, and the placeholder names should start with the
     *        plugin name, to avoid collisions.
     */
    public static function get_settings_sql($quizid) {
        return array('qaun.enabled as usernumattemptsenabled',
                     'LEFT JOIN {qa_usernumattempts} qaun ON qaun.quizid = quiz.id ',
                     array());
    }

    public function is_enabled() {
        global $DB;

        $params = array('quizid' => $this->quiz->id);
        $enabled = $DB->get_field('qa_usernumattempts', 'enabled', $params);
        return $enabled;
    }
}
