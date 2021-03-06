<?php
/**
 * Copyright (c) 2011 Bart Visscher <bartv@thisnet.nl>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

OCP\JSON::checkLoggedIn();
OCP\JSON::callCheck();

$id = $_POST['id'];

$permissions = OC_Calendar_App::getPermissions($id, OC_Calendar_App::EVENT);
if(!$permissions & OCP\Share::PERMISSION_UPDATE) {
	OCP\JSON::error(array('message'=>'permission denied'));
	exit;
}

$vcalendar = OC_Calendar_App::getVCalendar($id, false, false);
$vevent = $vcalendar->VEVENT;

$delta = new DateInterval('P0D');
$delta->d = $_POST['dayDelta'];
$delta->i = $_POST['minuteDelta'];

OC_Calendar_App::isNotModified($vevent, $_POST['lastmodified']);

$dtend = OC_Calendar_Object::getDTEndFromVEvent($vevent);
$end_type = $dtend->getDateType();
$dtend->setDateTime($dtend->getDateTime()->add($delta), $end_type);
unset($vevent->DURATION);

$vevent->setDateTime('LAST-MODIFIED', 'now', Sabre_VObject_Property_DateTime::UTC);
$vevent->setDateTime('DTSTAMP', 'now', Sabre_VObject_Property_DateTime::UTC);

OC_Calendar_Object::edit($id, $vcalendar->serialize());
$lastmodified = $vevent->__get('LAST-MODIFIED')->getDateTime();
OCP\JSON::success(array('lastmodified'=>(int)$lastmodified->format('U')));