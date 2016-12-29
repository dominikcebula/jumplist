<?php

/**
 * jumplist
 * @category tools
 *
 * @author Dominik Cebula dominikcebula@gmail.com
 * @copyright Dominik Cebula dominikcebula@gmail.com
 * @license GNU_GPL_v2
 * @version 1.0
 */
class jumplist extends Module {

    private $id_link;
    private $idx;
    private $title;
    private $link;
    private $error;

    public function __construct() {
        $this->name = 'jumplist';
        $this->version = '1.0';
        $this->tab = 'Tools';

        parent::__construct();

        $this->displayName = $this->l('Jump list support for IE9');
        $this->description = $this->l('This module gives you ability to create jumplists for Internet Explorer 9 pinned pages');
    }

    public function install() {
        if (parent::install()
                && $this->registerHook('header')
                && Db::getInstance()->Execute('CREATE  TABLE `' . _DB_PREFIX_ . 'jumplist` (
                                                   `id_link` INT NOT NULL AUTO_INCREMENT,
                                                   `idx` INT NOT NULL,
                                                   `title` VARCHAR(256) NOT NULL,
                                                   `link` VARCHAR(512) NOT NULL,
                                                   PRIMARY KEY (`id_link`))')
                && $this->putInitData())
            return true;
        else
            return false;
    }

    public function uninstall() {
        if (parent::uninstall()
                && $this->unregisterHook('jumplist')
                && Db::getInstance()->Execute('DROP TABLE `' . _DB_PREFIX_ . 'jumplist`'))
            return true;
        else
            return false;
    }

    public function validate() {
        if (strlen($this->id_link) == 0 || !is_numeric($this->id_link))
            $this->error = $this->l('task id error');
        else if (!preg_match('/^[A-Za-z0-9\?\!\-\:\/\.\=\_ ]+$/m', $this->title))
            $this->error = $this->l('title is invalid');
        else if (!preg_match('/^[A-Za-z0-9\?\!\-\:\/\.\=\_]+$/m', $this->link))
            $this->error = $this->l('link is invalid');
        else if (!preg_match('/^[0-9]+$/m', $this->idx))
            $this->error = $this->l('index field invalid');
        else
            $this->error = null;
    }

    public function putInitData() {
        $db = Db::getInstance();
        $ret = true;
        $ret = $ret && $db->Execute('INSERT INTO `' . _DB_PREFIX_ . 'jumplist` (id_link,idx,title,link) VALUES (1,1,"History and details of my orders", "/history.php")');
        $ret = $ret && $db->Execute('INSERT INTO `' . _DB_PREFIX_ . 'jumplist` (id_link,idx,title,link) VALUES (2,2,"My credit slips", "/order-slip.php")');
        $ret = $ret && $db->Execute('INSERT INTO `' . _DB_PREFIX_ . 'jumplist` (id_link,idx,title,link) VALUES (3,3,"My addresses", "/addresses.php")');
        $ret = $ret && $db->Execute('INSERT INTO `' . _DB_PREFIX_ . 'jumplist` (id_link,idx,title,link) VALUES (4,4,"My personal information", "/identity.php")');
        $ret = $ret && $db->Execute('INSERT INTO `' . _DB_PREFIX_ . 'jumplist` (id_link,idx,title,link) VALUES (5,5,"My vouchers", "/discount.php")');
        return $ret;
    }

    public function save() {
        if ($this->id_link == -1) {
            $sql = sprintf("insert into %sjumplist(id_link, idx, title, link)
                              values(0, %d, '%s', '%s')",
                            _DB_PREFIX_,
                            $this->idx,
                            $this->title,
                            $this->link);
            if (!Db::getInstance()->Execute($sql))
                $this->error = 'failed to save data';
            else
                $this->id_link = Db::getInstance()->Insert_ID();
        } else {
            $sql = sprintf("update %sjumplist
                              set idx=%d,
                                  title='%s',
                                  link='%s'
                              where id_link=%d",
                            _DB_PREFIX_,
                            $this->idx,
                            $this->title,
                            $this->link,
                            $this->id_link);
            if (!Db::getInstance()->Execute($sql))
                $this->error = 'failed to save data';
        }

        if ($this->error == null) {
            if (Tools::getValue('delicon'))
                $this->deleteIcon();
            else
                $this->saveIcon();
        }
    }

    public function deleteIcon() {
        $file = dirname(__FILE__) . '/icons/' . $this->id_link . '.ico';
        if (file_exists($file))
            unlink($file);
    }

    public function saveIcon() {
        if (!key_exists('file', $_FILES) || $_FILES['file']['size'] == 0)
            return;
        if ($_FILES['file']['error'] > 0) {
            $this->error = 'file uploading error';
            return;
        }

        $name = $_FILES['file']['name'];
        if ($_FILES['file']['type'] != 'image/x-icon' || substr($name, strlen($name) - 4, 4) != '.ico')
            $this->error = 'only ico files are accepted';

        if ($this->error == null) {
            $this->deleteIcon();
            move_uploaded_file($_FILES["file"]["tmp_name"], dirname(__FILE__) . '/icons/' . $this->id_link . '.ico');
        }
    }

    public function getIcon($iconid) {
        $path = dirname(__FILE__) . '/icons/' . $iconid . '.ico';
        if (!file_exists($path))
            return "";
        else
            return _PS_BASE_URL_ . '/modules/jumplist/icons/' . $iconid . '.ico';
    }

    public function delete() {
        $sql = sprintf("delete from %sjumplist
                              where id_link=%d",
                        _DB_PREFIX_,
                        $this->id_link);

        if (!Db::getInstance()->Execute($sql))
            $this->error = 'error while deleting entry';
    }

    public function fromRequest() {
        $this->id_link = Tools::getValue('id');
        $this->title = Tools::getValue('title');
        $this->link = Tools::getValue('link');
        $this->idx = Tools::getValue('idx');
    }

    public function getRequest() {
        return 'index.php?tab=' . Tools::getValue('tab') . '&configure=' . Tools::getValue('configure') . '&token=' . Tools::getValue('token');
    }

    public function getJumpList() {
        $data = Db::getInstance()->ExecuteS('select * from ' . _DB_PREFIX_ . 'jumplist order by idx, id_link');
        for ($i = 0; $i < count($data); $i++)
            $data[$i]['icon_url'] = $this->getIcon($data[$i]['id_link']);
        return $data;
    }

    public function getById($taskid) {
        $data = Db::getInstance()->ExecuteS('select * from ' . _DB_PREFIX_ . 'jumplist where id_link=' . intval($taskid));
        if (count($data) > 0) {
            $this->id_link = $data[0]['id_link'];
            $this->idx = $data[0]['idx'];
            $this->title = $data[0]['title'];
            $this->link = $data[0]['link'];
        }
    }

    public function getContent() {
        global $smarty;
        $this->error = null;
        $this->title = $this->link = "";
        $this->idx = 0;

        if (Tools::getValue('canceltask')) {
            $_POST['addtask'] = '';
            $_POST['edittask'] = '';
            $_POST['savetask'] = '';
        }

        if (Tools::getValue('savetask')) {
            $this->fromRequest();
            $this->validate();

            if ($this->error == null)
                $this->save();

            if ($this->error == null) {
                $_POST['addtask'] = '';
                $_POST['edittask'] = '';
            }
        } else if (Tools::getValue('deltask')) {
            $this->id_link = Tools::getValue('id');
            $this->delete();
        }

        if (Tools::getValue('addtask')) {
            $smarty->assign('id', -1);
            $smarty->assign('title', $this->title);
            $smarty->assign('link', $this->link);
            $smarty->assign('idx', $this->idx);
            $smarty->assign('error', $this->error);
            return $this->display(__FILE__, 'form.tpl');
        } else if (Tools::getValue('edittask')) {
            $this->getById(Tools::getValue('id'));
            $smarty->assign('id', $this->id_link);
            $smarty->assign('title', $this->title);
            $smarty->assign('link', $this->link);
            $smarty->assign('idx', $this->idx);
            $smarty->assign('error', $this->error);
            return $this->display(__FILE__, 'form.tpl');
        } else {
            $smarty->assign('request', $this->getRequest());
            $smarty->assign('jumps', $this->getJumpList());
            $smarty->assign('error', $this->error);
            return $this->display(__FILE__, 'list.tpl');
        }
    }

    public function hookHeader() {
        $str = "\r\n";
        $str.='<script type="text/javascript">' . "\r\n";
        $str.='if (navigator.appVersion.indexOf("MSIE 9.0")>0) {' . "\r\n";
        $str.='window.external.msSiteModeCreateJumplist("Tasks");' . "\r\n";
        $str.='window.external.msSiteModeClearJumplist();' . "\r\n";
        $jumps = $this->getJumpList();
        for ($i = count($jumps) - 1; $i >= 0; $i--) {
            $el = $jumps[$i];
            $str.='window.external.msSiteModeAddJumpListItem("' . $el['title'] . '", "' . $el['link'] . '","' . $el['icon_url'] . '");' . "\r\n";
        }
        $str.='}' . "\r\n";
        $str.='</script>' . "\r\n";
        return $str;
    }

}

?>
