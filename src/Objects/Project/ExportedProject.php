<?php

namespace TildaTools\Tilda\Objects\Project;

use TildaTools\Tilda\Objects\BaseObject;
use TildaTools\Tilda\Objects\Asset;

/**
 * Class Project
 * @package TildaTools\Tilda\Objects
 *
 * @property int $id
 * @property  string $title
 * @property string $descr
 * @property string $customdomain
 * @property string $export_csspath
 * @property string $export_jspath
 * @property string $export_imgpath
 * @property int $indexpageid
 * @property string $customcssfile
 * @property string $favicon
 * @property int $page404id
 * @property string $htaccess
 * @property Asset[] $images
 * @property Asset[] $css
 * @property Asset[] $js
 */
class ExportedProject extends BaseObject
{

}