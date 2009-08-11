<?php
/**
  * Simple file which allows fetching of backup files
  * @category admin
  *
  * @author Andrew Brampton
  * @copyright Andrew Brampton
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 0.1
  *
  */

define('PS_ADMIN_DIR', getcwd());

include(PS_ADMIN_DIR.'/../config/config.inc.php');

/* Header can't be included, so cookie must be created here */
$cookie = new Cookie('psAdmin');
if (!$cookie->id_employee)
	Tools::redirect('login.php');

$tabAccess = Profile::getProfileAccess($cookie->profile, Tab::getIdFromClassName('AdminBackup'));

if ($tabAccess['view'] !== '1')
	die (Tools::displayError('You do not have permission to view here'));

$backupdir = realpath( PS_ADMIN_DIR . '/backups/');

if ($backupdir === false)
	die (Tools::displayError('Backups directory does not exist'));
	
if (!$backupfile = Tools::getValue('filename'))
	die (Tools::displayError('no file specified'));

// Check the realpath so we can validate the backup file is under the backup directory
$backupfile = realpath($backupdir.'/'.$backupfile);

if ($backupfile === false OR strncmp($backupdir, $backupfile, strlen($backupdir)) != 0 )
	die (Tools::displayError());

if (substr($backupfile, -4) == '.bz2')
    $contentType = 'application/x-bzip2';
else if (substr($backupfile, -3) == '.gz')
    $contentType = 'application/x-gzip';
else
    $contentType = 'text/x-sql';
$fp = @fopen($backupfile, 'r'); 

if ($fp === false)
	die (Tools::displayError('Unable to open backup file').' "'.addslashes($backupfile).'"');

// Add the correct headers, this forces the file is saved
header('Content-Type: '.$contentType);
header('Content-Disposition: attachment; filename="'.Tools::getValue('filename'). '"');

$ret = @fpassthru($fp);

fclose($fp);

if ($ret === false)
	die (Tools::displayError('Unable to display backup file').' "'.addslashes($backupfile).'"');


?>