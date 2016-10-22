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
 * Unit tests for the quizaccess_usernumattempts plugin.
 *
 * @package     quizaccess_numattempts
 * @category    quizaccess
 * @category    phpunit
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   2014 onwards Valery Fremaux
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot.'/mod/quiz/accessrule/usernumattempts/rule.php');

/**
 * Unit tests for the quizaccess_usernumattempts plugin.
 *
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quizaccess_numattempts_testcase extends advanced_testcase {

    public function test_user_num_attempts_access_rule() {
        global $USER;

        $course = $this->getDataGenerator()->create_course();

        $generator = $this->getDataGenerator()->get_plugin_generator('mod_quiz');
        $quizrec = $generator->create_instance(array('course' => $course->id));

        $cm = get_coursemodule_from_instance('quiz', $quizrec->id);
        $quizobj = new quiz($quizrec, $cm, null);
        $rule = new quizaccess_usernumattempts($quizobj, 0);

        $user1 = $this->getDataGenerator()->create_user();
        $user1 = $this->getDataGenerator()->set_user_credits($user1, $quizrec->id, 3);

        $user2 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->set_user_credits($user1, $quizrec->id, 10);

        $this->assertEquals($rule->description(),
            get_string('attemptsallowedn', 'quizaccess_usernumattempts', 3));

        $this->setUser($user1);

        $this->assertFalse($rule->prevent_new_attempt(0, $attempt));
        $this->assertFalse($rule->prevent_new_attempt(2, $attempt));
        $this->assertEquals($rule->prevent_new_attempt(3, $attempt),
            get_string('nomoreattempts', 'quiz'));
        $this->assertEquals($rule->prevent_new_attempt(666, $attempt),
            get_string('nomoreattempts', 'quiz'));

        $this->setUser($user2);

        $this->assertFalse($rule->prevent_new_attempt(0, $attempt));
        $this->assertFalse($rule->prevent_new_attempt(5, $attempt));
        $this->assertEquals($rule->prevent_new_attempt(10, $attempt),
            get_string('nomoreattempts', 'quiz'));
        $this->assertEquals($rule->prevent_new_attempt(666, $attempt),
            get_string('nomoreattempts', 'quiz'));

        $this->setUser($user1);

        $this->assertFalse($rule->is_finished(0, $attempt));
        $this->assertFalse($rule->is_finished(2, $attempt));
        $this->assertTrue($rule->is_finished(3, $attempt));
        $this->assertTrue($rule->is_finished(666, $attempt));

        $this->setUser($user2);

        $this->assertFalse($rule->is_finished(0, $attempt));
        $this->assertFalse($rule->is_finished(5, $attempt));
        $this->assertTrue($rule->is_finished(10, $attempt));
        $this->assertTrue($rule->is_finished(666, $attempt));

        $this->assertFalse($rule->prevent_access());
        $this->assertFalse($rule->end_time($attempt));
        $this->assertFalse($rule->time_left_display($attempt, 0));
    }
}
