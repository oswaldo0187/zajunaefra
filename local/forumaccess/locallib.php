<?php
namespace local_forumaccess;

use core_courseformat\output\local\content\cm as core_cm;
use cm_info;

class custom_cm extends core_cm {

    public function __construct($format, $section, cm_info $mod, $displayoptions = []) {
        parent::__construct($format, $section, $mod, $displayoptions);
    }

    public function export_for_template(\renderer_base $output): array {
        $data = parent::export_for_template($output);

        global $DB;
        if ($this->mod->modname === 'forum') {
            $forum = $DB->get_record('forum', ['id' => $this->mod->instance], '*', IGNORE_MISSING);

            if ($forum && $forum->allowpostsfrom) {
                $data['allowpostsfrom'] = $forum->allowpostsfrom;
                $data['allowpostsfromformatted'] = userdate($forum->allowpostsfrom);

                // 🚨 Aquí añades la lógica para bloquear el foro si aún no está abierta la fecha
                if (time() < $forum->allowpostsfrom) {
                    $data['forumblocked'] = true; // 👈 Esta línea es la clave
                }
            }
        }

        return $data;
    }
}