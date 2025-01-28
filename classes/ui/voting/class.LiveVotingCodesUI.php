<?php
declare(strict_types=1);
/**
 * This file is part of the LiveVoting Repository Object plugin for ILIAS.
 * This plugin allows to create real time votings within ILIAS.
 *
 * The LiveVoting Repository Object plugin for ILIAS is open-source and licensed under GPL-3.0.
 * For license details, visit https://www.gnu.org/licenses/gpl-3.0.en.html.
 *
 * To report bugs or participate in discussions, visit the Mantis system and filter by
 * the category "LiveVoting" at https://mantis.ilias.de.
 *
 * More information and source code are available at:
 * https://github.com/surlabs/LiveVoting
 *
 * If you need support, please contact the maintainer of this software at:
 * info@surlabs.es
 *
 */

namespace LiveVoting\UI;

use ilCtrlException;
use ILIAS\UI\Component\Input\Container\Form\Standard;
use ilLiveVotingPlugin;
use ilObjLiveVotingGUI;
use LiveVoting\platform\LiveVotingException;
use LiveVoting\votings\LiveVoting;
use ui\LiveVotingCodesTable;

/**
 * Class LiveVotingCodesUI
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 * @ilCtrl_IsCalledBy  ilObjLiveVotingGUI: ilObjPluginGUI
 */
class LiveVotingCodesUI
{
    private LiveVoting $liveVoting;

    public function __construct(LiveVoting $liveVoting)
    {
        $this->liveVoting = $liveVoting;
    }

    /**
     * @throws ilCtrlException
     * @throws LiveVotingException
     */
    public function showCodes(ilObjLiveVotingGUI $parent): string
    {
        global $DIC;

        $plugin = ilLiveVotingPlugin::getInstance();

        $DIC->ui()->mainTemplate()->addCss($plugin->getDirectory() . "/templates/css/fix_table_width.css");
        $DIC->ui()->mainTemplate()->addJavaScript($plugin->getDirectory() . "/templates/js/codes_tab.js");

        $this->buildToolbar();

        $codes_data = new LiveVotingCodesTable();

        $this->liveVoting->loadCodes();

        $codes = $this->liveVoting->getCodes();

        foreach ($codes as $key => $code) {
            $button = $DIC->ui()->factory()->button()->standard($plugin->txt("edit_code"), "")->withOnLoadCode(function ($id) use ($code) {
                return "
                    $('#$id').attr('livevoting-code', '{$code["code"]}');
                    $('#$id').attr('livevoting-votes', '{$code["value"]}');
                    $('#$id').attr('modal-opener', 'change_votes_modal');
                ";
            });

            $codes[$key]["actions"] = $DIC->ui()->renderer()->render($button);
        }

        $codes_data->setRecords($codes);

        $table = $DIC->ui()->factory()->table()->data(
            '',
            [
                'code' => $DIC->ui()->factory()->table()->column()->text($plugin->txt("codes_table_code"))->withIsSortable(true),
                'used' => $DIC->ui()->factory()->table()->column()->text($plugin->txt("codes_table_used"))->withIsSortable(true),
                'value' => $DIC->ui()->factory()->table()->column()->text($plugin->txt("codes_table_votes"))->withIsSortable(true),
                'actions' => $DIC->ui()->factory()->table()->column()->text($plugin->txt("common_actions"))->withIsSortable(false)
            ],
                $codes_data
        );

        return $DIC->ui()->renderer()->render($table->withRequest($DIC->http()->request())) . $DIC->ui()->renderer()->render($this->buildModals());
    }

    /**
     * @throws ilCtrlException
     */
    private function buildToolbar(): void
    {
        global $DIC;

        $number = $DIC->ui()->factory()->input()->field()->numeric("")->withDedicatedName("number")->withOnLoadCode(function ($id) use ($DIC) {
            return "
                $('#$id').attr('name', 'number');
                $('#$id').attr('placeholder', '{$DIC->language()->txt('rep_robj_xlvo_number_of_codes')}');
                $('#$id').parent().parent().css('margin-bottom', '0px');
                $('#$id').parent().css('width', '100%');
                $('[for=$id]').remove();
            ";
        });

        $generate = $DIC->ui()->factory()->button()->standard($DIC->language()->txt('rep_robj_xlvo_generate_codes'), "");

        $clear = $DIC->ui()->factory()->button()->standard($DIC->language()->txt('rep_robj_xlvo_clear_codes'), $DIC->ctrl()->getLinkTargetByClass(ilObjLiveVotingGUI::class, 'clearCodes'));

        $DIC->toolbar()->setOpenFormTag(true);
        $DIC->toolbar()->addComponent($number);
        $DIC->toolbar()->addComponent($generate);
        $DIC->toolbar()->setFormAction($DIC->ctrl()->getLinkTargetByClass(ilObjLiveVotingGUI::class, 'generateCodes'));
        $DIC->toolbar()->setCloseFormTag(true);
        $DIC->toolbar()->addComponent($clear);
    }

    /**
     * @throws ilCtrlException
     */
    private function buildModals(): array
    {
        global $DIC;

        $plugin = ilLiveVotingPlugin::getInstance();

        $modals = array(
            "change_votes_modal" => [$DIC->ui()->renderer()->render($this->buildEditCodeForm()), $plugin->txt("edit_code")],
        );

        $rendered = array();

        foreach ($modals as $key => $modal) {
            $rendered[$key] = $DIC->ui()->factory()->modal()->lightbox($DIC->ui()->factory()->modal()->lightboxTextPage($modal[0], $modal[1]));
        }

        foreach ($rendered as $key => $modal) {
            $rendered[$key] = $modal->withOnLoadCode(function ($id) use ($key, $modal) {
                return "
                    $('#$id').attr('$key', true);
                    $('#$id').attr('modal-signal', '" . $modal->getShowSignal() . "');
                ";
            });
        }

        return $rendered;
    }

    /**
     * @throws ilCtrlException
     */
    public static function buildEditCodeForm(): Standard
    {
        global $DIC;

        $plugin = ilLiveVotingPlugin::getInstance();

        $inputs = array(
            "code" => $DIC->ui()->factory()->input()->field()->text($plugin->txt("codes_table_code"))->withOnLoadCode(function ($id) {
                return "
                    $('#$id').attr('surname', 'code');
                    $('#$id').attr('readonly', 'readonly');
                ";
            }),
            "votes" => $DIC->ui()->factory()->input()->field()->numeric($plugin->txt("codes_table_votes"))->withRequired(true)->withOnLoadCode(function ($id) {
                return "
                    $('#$id').attr('surname', 'votes');
                ";
            }),
        );

        return $DIC->ui()->factory()->input()->container()->form()->standard($DIC->ctrl()->getFormActionByClass(ilObjLiveVotingGUI::class, 'editCode'), $inputs);
    }
}