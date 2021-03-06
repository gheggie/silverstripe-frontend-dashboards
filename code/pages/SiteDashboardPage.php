<?php

/**
 * @author marcus@silverstripe.com.au
 * @license BSD License http://silverstripe.org/bsd-license/
 */
class SiteDashboardPage extends Page
{
    private static $has_one = array(
        'FixedDashboard'        => 'DashboardPage',
    );
    
    public function getCMSFields() {
        $fields = parent::getCMSFields();
        
        $allboards = DashboardPage::get()->restrict();
        $boards = array();
        foreach ($allboards as $board) {
            $boards[$board->ID] = $board->Title . ' ' . $board->Owner()->Title;
        }
        
        $fields->addFieldToTab('Root.Main', DropdownField::create('FixedDashboardID', 'Specific dashboard to show', $boards)->setEmptyString('-- none --'), 'Content');
        $fields->removeByName('Content');
        return $fields;
    }
}

class SiteDashboardPage_Controller extends DashboardController
{

    private static $dependencies = array(
        'dataService'        => '%$DataService',
    );
    
    public function init()
    {
        $board = $this->data()->FixedDashboard();
        if ($board->ID && $board->canView()) {
            $this->currentDashboard = $board;
        }
        parent::init();
        
        Requirements::css('frontend-dashboards/thirdparty/aristo/aristo.css');
        
        if (class_exists('WebServiceController')) {
            Requirements::javascript('webservices/javascript/webservices.js');
        }
    }
    
    public function Link($action = null)
    {
        $dashboard = $this->currentDashboard;

        if ($dashboard && $dashboard->URLSegment != 'main') {
            $identifier = Member::get_unique_identifier_field();
            $identifier = $dashboard->Owner()->$identifier;
            
            $segment = $dashboard->URLSegment ? $dashboard->URLSegment : 'main';
            
            return Controller::join_links(
                $this->data()->Link(true), 'board', $segment, $dashboard->Owner()->ID, $action
            );
        } else {
            return $this->data()->Link($action ? $action : true);
        }
    }
}
