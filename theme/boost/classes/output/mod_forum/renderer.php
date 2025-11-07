<?php
namespace theme_boost\output\mod_forum;

use mod_forum\output\forum_post;
use mod_forum\renderer as core_renderer;
use context_module;

defined('MOODLE_INTERNAL') || die();

class renderer extends core_renderer {

    protected function render_forum_post(forum_post $post) {
        global $DB;

        $data = $post->export_for_template($this);
        $teacherreplies = [];
        $filteredreplies = [];

        $context = \context_module::instance($post->get_forum()->cmid);
        $replies = $post->get_children();

        foreach ($replies as $replypost) {
            $user = $DB->get_record('user', ['id' => $replypost->get_post()->userid], '*', IGNORE_MISSING);
            if (!$user) {
                continue;
            }
            // Verifica si es docente (rolid 3 por defecto)
            if (user_has_role_assignment($user->id, 3, $context->id)) {
                $teacherreplies[] = [
                    'message' => format_text($replypost->get_post()->message, FORMAT_HTML),
                    'fullname' => fullname($user)
                ];
            } else {
                $filteredreplies[] = $replypost;
            }
        }

        // Sobrescribe replies con solo los que no son docentes
        $data['replies'] = array_map(function($replypost) {
            return $replypost->export_for_template($this);
        }, $filteredreplies);

        // Agrega las respuestas del docente
        $data['teacherreplies'] = $teacherreplies;

        return $this->render_from_template('mod_forum/forum_discussion_post', $data);
    }
}
