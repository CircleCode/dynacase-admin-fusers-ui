<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

function fusers_get_datatable_info(Action & $action)
{
    $filter = 0;
    $start = intval($action->getArgument('iDisplayStart'));
    $limit = intval($action->getArgument('iDisplayLength'));
    $sEcho = intval($action->getArgument('sEcho'));
    $type = $action->getArgument('type');
    $group = $action->getArgument("group");
    $data = array();
    
    $out = array(
        "sEcho" => $sEcho
    );
    $err = "";
    $s = null;
    
    switch ($type) {
        case "role":
            $s = new SearchDoc($action->dbaccess, "ROLE");
            break;

        case "group":
            $s = new SearchDoc($action->dbaccess, "IGROUP");
            break;

        case "user":
            $s = new SearchDoc($action->dbaccess, "IUSER");
            if ($group) $s->addFilter(sprintf("'%s' = any(us_idgroup)", $group));
            break;

        default:
            $err = sprintf("wrong type got : %s", $type);
        }
        
        if (!$err) {
            $displayLength = intval($action->getParam("FUSERS_DISPLAYLENGTH"));
            if ($displayLength != $limit) {
                $action->setParamU("FUSERS_DISPLAYLENGTH", $limit);
            }
            $out["iTotalRecords"] = $s->onlyCount();
            $s->reset();
            $sortArg = $action->getArgument(sprintf("iSortCol_%d", 0) , null);
            if ($sortArg !== null) {
                $s->setOrder($action->getArgument('mDataProp_' . $sortArg) . " " . $action->getArgument(sprintf("sSortDir_%d", 0)));
            }
            $searchArray = array(
                "icon",
                "id"
            );
            for ($index = 0; $index < $action->getArgument('iColumns'); $index++) {
                $search = $action->getArgument('sSearch_' . $index);
                $searchArray[] = $action->getArgument('mDataProp_' . $index);
                if ($search) {
                    $field = $action->getArgument('mDataProp_' . $index);
                    if ($field == "icon") {
                        $s->addFilter(sprintf("%s = '%s'", "fromid", pg_escape_string($search)));
                    } else $s->addFilter(sprintf("%s ~* '%s'", $field, pg_escape_string($search)));
                    $filter++;
                }
            }
            
            $s->setStart($start);
            $s->setSlice($limit);
            $s->returnsOnly($searchArray);
            $searchInfos = $s->search();
            foreach ($searchInfos as $infos) {
                $infos["icon"] = $action->parent->getImageLink($infos["icon"], true, 18);
                $data[] = $infos;
            }
            $out["iTotalDisplayRecords"] = count($data);
        } else {
            $out["errors"] = $err;
            $out["iTotalRecords"] = 0;
        }
        
        if ($filter == 0) $out["iTotalDisplayRecords"] = $out["iTotalRecords"];
        else {
            $s->reset();
            $out["iTotalDisplayRecords"] = $s->onlyCount();
        }
        $out['aaData'] = $data;
        $action->lay->template = json_encode($out);
        $action->lay->noparse = true;
        
        header('Content-type: application/json');
        return $out;
    }
    