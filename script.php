<?php

$coordinates = array();

$coordinates['VACUUM_NULL'][] = "";
$coordinates['VACUUM_ENTRANCE'][] = "25179,23770,27829,26020,1";
$coordinates['VACUUM_CORRIDOR'][] = "27012,23695,30012,24795,1";
$coordinates['VACUUM_KITCHEN'][] = "29430,21778,32330,24678,1";
$coordinates['VACUUM_KITCHEN_CAT'][] = "30687,23001,32537,24701,2";
$coordinates['VACUUM_KITCHEN_FURNITURE'][] = "29358,21657,32558,23757,1";
$coordinates['VACUUM_LIVING_ROOM'][] = "21502,21653,25652,27403,1";
$coordinates['VACUUM_LIVING_ROOM_TOP'][] = "21502,24503,25652,27403,1";
$coordinates['VACUUM_LIVING_ROOM_BOTTOM'][] = "21509,21625,25609,24875,1";
$coordinates['VACUUM_LIVING_ROOM_BOTTOM_NO_OFFICE'][] = "21483,22852,25783,24802,1";
$coordinates['VACUUM_LIVING_ROOM_ONLY_OFFICE'][] = "21580,21534,23530,23384,1";

$coordinates['VACUUM_GO_KITCHEN'][] = "31100,23800";

$coordinates['VACUUM_ALL'][] = $coordinates['VACUUM_ENTRANCE'][0];
$coordinates['VACUUM_ALL'][] = $coordinates['VACUUM_LIVING_ROOM'][0];
$coordinates['VACUUM_ALL'][] = $coordinates['VACUUM_CORRIDOR'][0];
$coordinates['VACUUM_ALL'][] = $coordinates['VACUUM_KITCHEN_ALL'][0];

$coordinates['VACUUM_EXCEPT_KITCHEN'][] = $coordinates['VACUUM_ENTRANCE'][0];
$coordinates['VACUUM_EXCEPT_KITCHEN'][] = $coordinates['VACUUM_LIVING_ROOM'][0];
$coordinates['VACUUM_EXCEPT_KITCHEN'][] = $coordinates['VACUUM_CORRIDOR'][0];

$coordinates['VACUUM_EXCEPT_LIVING_ROOM'][] = $coordinates['VACUUM_ENTRANCE'][0];
$coordinates['VACUUM_EXCEPT_LIVING_ROOM'][] = $coordinates['VACUUM_CORRIDOR'][0];
$coordinates['VACUUM_EXCEPT_LIVING_ROOM'][] = $coordinates['VACUUM_KITCHEN'][0];

$coordinates['VACUUM_EXCEPT_CORRIDOR'][] = $coordinates['VACUUM_ENTRANCE'][0];
$coordinates['VACUUM_EXCEPT_CORRIDOR'][] = $coordinates['VACUUM_LIVING_ROOM'][0];
$coordinates['VACUUM_EXCEPT_CORRIDOR'][] = $coordinates['VACUUM_KITCHEN'][0];

$coordinates['VACUUM_EXCEPT_ENTRANCE'][] = $coordinates['VACUUM_LIVING_ROOM'][0];
$coordinates['VACUUM_EXCEPT_ENTRANCE'][] = $coordinates['VACUUM_CORRIDOR'][0];
$coordinates['VACUUM_EXCEPT_ENTRANCE'][] = $coordinates['VACUUM_KITCHEN'][0];

// #[Bureau][ACTIONS VACUUM][rotation_map]# = Jeedom Virtuals Infos
$cmd = cmd::byString("#[Bureau][ACTIONS VACUUM][rotation_map]#");
$rotatation = $cmd->execCmd();


$cmd = cmd::byString("#[Bureau][ACTIONS VACUUM][centre_x]#");
$centerX = $cmd->execCmd();


$cmd = cmd::byString("#[Bureau][ACTIONS VACUUM][centre_y]#");
$centerY = $cmd->execCmd();

$center = array($centerX, $centerY);

function rotate($x, $y, $angle, $center)
{
    if ($angle == 0) {
        // Adjust axis here if the dock is moved
        $x = $x + 0;
        $y = $y + 0;
        return array($x, $y);
    }

    $angle *= M_PI / 180;
    $xM = $x - $center[0];
    $yM = $y - $center[1];

    $x = $xM * cos($angle) + $yM * sin($angle) + $center[0];
    $y = $xM * sin($angle) + $yM * cos($angle) + $center[0];
    return array(round($x), round($y));
}

function generate($coordinates, $job, $rotation, $center)
{
    if (count($coordinates[$job]) < 1) return "AUCUN";
    $areaDef = array();
    foreach ($coordinates[$job] as $aZone) {
        $area = explode(',', $aZone);
        $coord = array();
        list($xa, $ya) = rotate($area[0], $area[1], $rotation, $center);
        if ($area[2] > 0) {
            list($xb, $yb) = rotate($area[2], $area[3], $rotation, $center);
            if ($xa < $xb) {
                $coord[0] = $xa;
                $coord[1] = 0;
                $coord[2] = $xb;
            } else {
                $coord[0] = $xb;
                $coord[1] = 0;
                $coord[2] = $xa;
            }

            if ($ya < $yb) {
                $coord[0] = $ya;
                $coord[3] = $yb;
            } else {
                $coord[0] = $yb;
                $coord[3] = $ya;
            }

            $coord[4] = $area[4];
            $areaDef[] = '' . implode(',', $coord) . ']';
        } else {
            $areaDef[] = $xa . ',' . $ya;
        }
    }
    return implode(',', $areaDef);
}

$cmd = cmd::byString("#[Bureau][ACTIONS VACUUM][Ordre]#");
$job = $cmd->execCmd();

$newPos = generate($coordinates, $job, $rotation, $center);

$cmd = cmd::byString("#[Bureau][ACTIONS VACUUM][coordonnees]#");
$cmd->event($newPos);
