<?php

namespace Simpletipp\Modules;

use Simpletipp\SimpletippModule;

/**
 * Class SimpletippHighscore
 *
 * @copyright  Martin Kozianka 2014-2019
 * @author     Martin Kozianka <martin@kozianka.de>
 * @package    Controller
 */
// Idee: HallOfFame

class SimpletippHighscore extends SimpletippModule
{
    private $filter = null;

    protected $strTemplate = 'simpletipp_highscore_default';

    public function generate()
    {
        if (TL_MODE == 'BE') {
            $this->Template = new \BackendTemplate('be_wildcard');
            $this->Template->wildcard = '### SimpletippHighscore ###';
            $this->Template->wildcard .= '<br/>' . $this->headline;
            return $this->Template->parse();
        }

        $this->strTemplate = $this->simpletipp_template;

        return parent::generate();
    }

    protected function compile()
    {

        // Filter
        $this->show = (\Input::get('show') !== null) ? urldecode(\Input::get('show')) : 'all';

        $this->Template->filter = $this->generateFilter();

        if ($this->show === 'bestof') {
            $this->Template->avatarActive = $this->avatarActive;
            $this->Template->tableClass = 'highscore_bestof';
            $this->Template->table = $this->bestOfTable();
            return;
        }

        $matchgroupName = null;
        if ($this->show === 'current') {
            $this->Template->tableClass = 'highscore_current';

            // get current matchgroup
            $result = $this->Database->prepare("SELECT groupName FROM tl_simpletipp_match
               WHERE leagueID = ? AND result != '' ORDER BY deadline DESC")->limit(1)
                ->execute($this->simpletipp->leagueID);

            if ($result->numRows == 0) {
                $result = $this->Database->prepare("SELECT groupName FROM tl_simpletipp_match
                   WHERE leagueID = ? ORDER BY deadline ASC")->limit(1)
                    ->execute($this->simpletipp->leagueID);
            }

            if ($result->numRows == 1) {
                $matchgroupName = $result->groupName;
            }
        } elseif ($this->show !== 'all') {
            $this->Template->tableClass = 'highscore_matchgroup';
            // show is matchgroupName
            $matchgroupName = $this->show;
        } else {
            $this->Template->tableClass = 'highscore_complete';
        }

        $table = $this->getHighscore($matchgroupName);

        $this->Template->avatarActive = $this->avatarActive;
        $this->Template->table = $table;

    }

    private function generateFilter()
    {
        $specialOptions = [
            'current' => [
                'href' => $this->addToUrl('show=current'),
                'active' => ($this->show == 'current'),
            ],
            'all' => [
                'href' => $this->addToUrl('show='),
                'active' => (!$this->show || $this->show == 'all'),
            ],
            'bestof' => [
                'href' => $this->addToUrl('show=bestof'),
                'active' => ($this->show == 'bestof'),
            ],
        ];

        $i = 0;
        $count = count($specialOptions);
        foreach ($specialOptions as $key => &$entry) {
            $entry['title'] = $GLOBALS['TL_LANG']['simpletipp']['highscore_' . $key][0];
            $entry['desc'] = $GLOBALS['TL_LANG']['simpletipp']['highscore_' . $key][1];

            $cssClasses = $key . ' count' . $count . ' pos' . $i;
            $cssClasses .= ($i == 0) ? ' first' : '';
            $cssClasses .= ($count === $i + 1) ? ' last' : '';
            $entry['selected'] = '';

            if ($entry['active']) {
                $cssClasses .= ' active';
                $entry['selected'] = ' selected="selected"';
            }
            $entry['cssClass'] = ' class="' . $cssClasses . '"';
            $i++;
        }

        $groupOptions = [];
        $i = 0;
        $count = count($this->simpletippGroups);
        $prefix = ' class="matchgroup count' . $count;
        foreach ($this->simpletippGroups as $mg) {
            $act = ($this->show == $mg->title);
            $cssClass = $prefix . (($i == 0) ? ' first' : '');
            $cssClass .= ($i + 1 == $count) ? ' last %s"' : ' %s"';

            $groupOptions[] = [
                'title' => $mg->short,
                'desc' => $mg->title,
                'href' => $this->addToUrl('show=' . $mg->title),
                'cssClass' => ($act) ? sprintf($cssClass, 'pos' . $i++ . ' active') : sprintf($cssClass, 'pos' . $i++),
                'selected' => ($act) ? ' selected="selected"' : '',
            ];
        }

        $tmpl = new \FrontendTemplate('simpletipp_filter');
        $tmpl->special_filter = $specialOptions;
        $tmpl->group_filter = $groupOptions;

        return $tmpl->parse();
    }

    private function bestOfTable()
    {
        $bestOf = $this->cache(__METHOD__);
        if ($bestOf != null) {
            return $bestOf;
        }
        $bestOf = [];

        // Alle bisher gespielten Gruppen holen
        $mgResult = $this->Database->prepare("SELECT DISTINCT groupName FROM tl_simpletipp_match WHERE
            deadline < ? ORDER BY LENGTH(groupName) ASC, groupName ASC")->execute($this->now);

        while ($mgResult->next()) {
            $matchgroupName = $mgResult->groupName;

            $matchGroupTable = $this->getHighscore($matchgroupName);

            foreach ($matchGroupTable as $row) {
                $currentRow = $bestOf[$row->member_id];
                if ($currentRow == null || (intval($currentRow->points) < intval($row->points))) {
                    $newRow = $row;
                    $newRow->groupName = $matchgroupName;
                    $bestOf[$row->member_id] = $newRow;

                }
            }
        }
        // Sortieren
        usort($bestOf, function ($a, $b) {

            // Punkte überprüfen
            if ($a->points > $b->points) {
                return -1;
            }

            if ($a->points < $b->points) {
                return 1;
            }

            // danach Tendenzen auswerten
            if ($a->sum_tendency > $b->sum_tendency) {
                return -1;
            }

            if ($a->sum_tendency < $b->sum_tendency) {
                return 1;
            }

            // danach Differenzen auswerten
            if ($a->sum_difference > $b->sum_difference) {
                return -1;
            }

            if ($a->sum_difference < $b->sum_difference) {
                return 1;
            }

            // danach Richtige auswerten (Kann eigentlich nicht eintreten!)
            if ($a->sum_perfect > $b->sum_perfect) {
                return -1;
            }

            if ($a->sum_perfect < $b->sum_perfect) {
                return 1;
            }

            return 0;

        });

        // CSS Klassen setzen
        $i = 1;
        foreach ($bestOf as &$row) {
            $row->cssClass = 'pos' . $i++ . ' ' . (($i % 2 == 0) ? ' odd' : ' even');
            $row->cssClass .= ($row->username == $this->User->username) ? ' current' : '';
        }

        // Ergebnis cachen
        $this->cache(__METHOD__, $bestOf);

        return $bestOf;
    }

} // END class SimpletippHighscore
