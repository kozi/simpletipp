<?php

$GLOBALS['TL_DCA']['tl_simpletipp_team'] = [

    // Config
    'config' => [
        'dataContainer' => 'Table',
        'switchToEdit' => true,
        'closed' => true,
        'enableVersioning' => true,
        'sql' => ['keys' => ['id' => 'primary']],
    ],
    // List
    'list' => [
        'sorting' => [
            'mode' => 2,
            'fields' => ['leagues ASC', 'name ASC'],
            'flag' => 1,
            'panelLayout' => 'filter, search, limit',
        ],
        'label' => [
            'fields' => ['logo', 'name', 'short', 'three'],
            'showColumns' => true,
            'label_callback' => ['tl_simpletipp_team', 'labelCallback'],
        ],
        'global_operations' => [
            'all' => [
                'label' => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset();"',
            ],
        ],
        'operations' => [

            'edit' => [
                'label' => &$GLOBALS['TL_LANG']['tl_simpletipp']['edit'],
                'href' => 'act=edit',
                'icon' => 'edit.gif',
            ],
            'delete' => [
                'label' => &$GLOBALS['TL_LANG']['tl_simpletipp']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.gif',
                'attributes' => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"',
            ],
        ],
    ],
    // Palettes
    'palettes' => ['default' => '{team_legend}, name, short, alias, three, logo'],

    // Fields
    'fields' => [

        'id' => ['sql' => "int(10) unsigned NOT NULL auto_increment"],
        'tstamp' => ['sql' => "int(10) unsigned NOT NULL default '0'"],
        'leagues' => [
            'label' => &$GLOBALS['TL_LANG']['tl_simpletipp_team']['leagues'],
            'sql' => "text NOT NULL",
            'search' => true,
        ],
        'name' => [
            'label' => &$GLOBALS['TL_LANG']['tl_simpletipp_team']['name'],
            'exclude' => true,
            'flag' => 1,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'tl_class' => 'long', 'readonly' => true],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'short' => [
            'label' => &$GLOBALS['TL_LANG']['tl_simpletipp_team']['short'],
            'exclude' => true,
            'flag' => 1,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'tl_class' => 'w50', 'maxlength' => 32],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'alias' => [
            'label' => &$GLOBALS['TL_LANG']['tl_simpletipp_team']['alias'],
            'exclude' => true,
            'flag' => 1,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'tl_class' => 'w50', 'maxlength' => 32],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'three' => [
            'label' => &$GLOBALS['TL_LANG']['tl_simpletipp_team']['three'],
            'exclude' => true,
            'flag' => 1,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'tl_class' => 'w50', 'maxlength' => 3],
            'sql' => "varchar(3) NOT NULL default ''",
        ],
        'logo' => [
            'label' => $GLOBALS['TL_LANG']['tl_simpletipp_team']['logo'],
            'exclude' => true,
            'search' => false,
            'sorting' => false,
            'inputType' => 'fileTree',
            'eval' => ['tl_class' => 'clr', 'mandatory' => false, 'files' => true, 'filesOnly' => true, 'fieldType' => 'radio'],
            'sql' => "binary(16) NULL",
        ],

    ], // fields END
];

/**
 * Class tl_simpletipp_team
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 * @copyright  Martin Kozianka 2014-2019
 * @author     Martin Kozianka <http://kozianka.de/>
 * @package    simpletipp
 */

class tl_simpletipp_team extends \Backend
{
    public function __construct()
    {
        parent::__construct();
    }

    public function labelCallback($row, $label, DataContainer $dc, $args = null)
    {
        $objFile = FilesModel::findByUuid($row['logo']);
        if ($objFile !== null) {
            // logo
            $args[0] = \Image::getHtml(\Image::get($objFile->path, 32, 32, 'box'), 'Wappen', 'class="wappen"');
        }

        return $args;
    }

}
