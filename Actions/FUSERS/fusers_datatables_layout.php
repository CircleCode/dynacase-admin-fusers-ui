<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

include_once ("FDL/freedom_util.php");
/**
 * Set layout for fusers datatables
 * @param Action $action
 * @throws Dcp\Exception
 * @throws Dcp\DocManager\Exception
 */
function fusers_datatables_layout(Action & $action)
{
    $type = $action->getArgument("type");
    $group = $action->getArgument("group");
    $displayLength = $action->getParam("FUSERS_DISPLAYLENGTH", "10");
    $thead = array();
    $list = array();
    switch ($type) {
        case "user":
            $thead = array(
                array(
                    "id" => "us_login",
                    "name" => _("login") ,
                    "value" => ""
                ) ,
                array(
                    "id" => "us_lname",
                    "name" => _("lastname") ,
                    "value" => ""
                ) ,
                array(
                    "id" => "us_fname",
                    "name" => _("firstname") ,
                    "value" => ""
                ) ,
                array(
                    "id" => "us_mail",
                    "name" => _("mail") ,
                    "value" => ""
                )
            );
            $list = getButtonList($action, "IUSER");
            break;

        case "role":
            $thead = array(
                array(
                    "id" => "title",
                    "name" => _("fusers:label") ,
                    "value" => ""
                )
            );
            $list = getButtonList($action, "ROLE");
            break;

        case "group":
            $thead = array(
                array(
                    "id" => "us_login",
                    "name" => _("login") ,
                    "value" => ""
                ) ,
                array(
                    "id" => "grp_name",
                    "name" => _("desc") ,
                    "value" => ""
                )
            );
            $list = getButtonList($action, "IGROUP");
            break;
    }
    array_pop($list);
    $action->lay->setBlockData("thead", $thead);
    $action->lay->eSet("fuserType", $type);
    $action->lay->eSet("fuserGroup", $group);
    $action->lay->eSet("fuserDisplayLength", $displayLength);
    $action->lay->set("style", (count($list) <= 0 ? "display:none;" : ""));
    $action->lay->eSetBlockData("urgtype", $list);
    $action->lay->set("imgsrc", $action->parent->getImageLink("iuser.png", true, 18));
    $action->lay->set("valuetype", "");
}

function getButtonList(Action & $action, $famid)
{
    $list = array();
    $doc = \Dcp\DocManager::getFamily($famid);
    $infos = $doc->getChildFam(-1, true);
    foreach ($infos as $info) {
        $list[] = array(
            "value" => $info["id"],
            "label" => $info["title"],
            "imgsrc" => $action->parent->getImageLink($info["icon"], true, 14) ,
            "imgclass" => ""
        );
    }
    $list[] = array(
        "value" => $doc->getPropertyValue("id") ,
        "label" => $doc->getTitle() ,
        "imgsrc" => $doc->getIcon("", 14) ,
        "imgclass" => ""
    );
    $action->lay->set("idmain", $doc->getPropertyValue("id"));
    $action->lay->eSet("titlemain", $doc->getTitle());
    $action->lay->set("imgsrcmain", $doc->getIcon("", 14));
    return $list;
}

function get_type_image(Action & $action)
{
    $out = array();
    $type = $action->getArgument("type");
    switch ($type) {
        case "user":
            $out = getButtonList($action, "IUSER");
            break;

        case "role":
            $out = getButtonList($action, "ROLE");
            break;

        case "group":
            $out = getButtonList($action, "IGROUP");
            break;
    }
    $out[] = array(
        "value" => "",
        "imgsrc" => $action->parent->getImageLink("user.png", true, 18) ,
        "label" => _("fusers:All") ,
        "imgclass" => "ui-icon ui-icon-radio-on"
    );
    $action->lay->template = json_encode($out);
    $action->lay->noparse = true;
    
    header('Content-type: application/json');
}
