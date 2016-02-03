<?php namespace KurtJensen\AuthNotice\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use Flash;
use KurtJensen\AuthNotice\Models\Message;
use KurtJensen\AuthNotice\Models\MessageMax;
use KurtJensen\AuthNotice\Models\Settings;

/**
 * Read Back-end Controller
 */
class Read extends Controller
{
    public $requiredPermissions = ['kurtjensen.authnotice.read'];

    public $implement = [
        'Backend.Behaviors.ListController',
        'Backend.Behaviors.FormController',
    ];

    public $formConfig = 'config_form.yaml';

    public $listConfig = 'config_list.yaml';

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('KurtJensen.AuthNotice', 'authnotice', 'read');
    }

    /**
     * Makes a request to the plugin authors message service
     */
    public function update($id)
    {
        parent::update($id);
        $this->vars['lang'] = Settings::get('read_lang', 'en');
    }

    /**
     * Makes a request to the plugin authors message service
     */
    public function onMarkRead($id)
    {
        if (post('id') && post('markread')) {
            if (!$message = Message::find($id)) {
                return;
            }
            $message->markRead();
        }
    }

    public function index_onRead()
    {
        if (($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds)) {

            Message::whereIn('id', $checkedIds)
                ->update(['read' => 1]);

            Flash::success('Successfully marked those messages.');
        }

        return $this->listRefresh();
    }

    public function index_onPurge()
    {
        $retention = Settings::get('retention', 30);
        $retentionDate = date('Y-m-d H:i:s', strtotime('-' . $retention . ' day'));

        $protectRows = MessageMax::lists('row_id');

        Message::whereNotIn('id', $protectRows)
            ->where('sent_at', '<', $retentionDate)
            ->where('read', 1)->delete();

        Flash::success('Successfully deleted read messages.');

        return $this->listRefresh();
    }

}
