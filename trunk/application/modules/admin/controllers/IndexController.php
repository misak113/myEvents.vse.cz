<?php

use Zette\UI\BaseController;
use app\services\TitleLoader;

/**
 * Created by JetBrains PhpStorm.
 * User: Michael
 * Date: 17.11.12
 * Time: 16:39
 * To change this template use File | Settings | File Templates.
 */
class Admin_IndexController extends BaseController {
    
    /** @var TitleLoader */
    protected $titleLoader;
    
    protected $eventPrototype;

    /**
     * @param TitleLoader $titleLoader 
     */
    public function setContext(TitleLoader $titleLoader) {
        $this->titleLoader = $titleLoader;
    }
    
    public function init(){
        $this->eventPrototype = Event::create();
    }

    public function loginAction() {
        $this->template->title = $this->titleLoader->getTitle('Admin:Index:login');
    }
    
    public function editAction() {
        $eventId = $this->_getParam('id');
        
        $this->template->title = $this->titleLoader->getTitle('Admin:Index:new');
        if($eventId){
            $this->view->event = $this->eventPrototype[$eventId];
        } else {
            $this->view->event = new Event();
        }
    }
    
    public function indexAction() {
        $this->template->title = $this->titleLoader->getTitle('Admin:Index:index');
        $this->template->events = $this->eventPrototype;
    }

}

class Event{
    public $fbUrl;
    public $nazev;
    public $datum;
    public $zacatek;
    public $konec;
    public $misto;
    public $kapacita;
    public $popis;
    //TODO: obrazek;
    public $sponzor;
    
    public static function create(){
        $events[1] = new Event();
        $events[1]->id = 1;
        $events[1]->datum = "1.11.2012";
        $events[1]->fbUrl = "https://www.facebook.com/myEvents.vse.cz";
        $events[1]->kapacita = 300;
        $events[1]->konec = "20:00";
        $events[1]->misto = "RB 101";
        $events[1]->nazev = "Setkání filatelistů 1";
        $events[1]->popis = "Setkání filatelistů v RB 101. Bude sranda.";
        $events[1]->sponzor = "";
        $events[1]->zacatek = "19:00";
        
        $events[2] = new Event();
        $events[2]->id = 2;
        $events[2]->datum = "5.11.2012";
        $events[2]->fbUrl = "https://www.facebook.com/myEvents.vse.cz";
        $events[2]->kapacita = 300;
        $events[2]->konec = "20:00";
        $events[2]->misto = "RB 101";
        $events[2]->nazev = "Jak fungují filateistické burzy";
        $events[2]->popis = "Setkání filatelistů v RB 101. Bude sranda.";
        $events[2]->sponzor = "";
        $events[2]->zacatek = "19:00";
        
        $events[3] = new Event();
        $events[3]->id = 3;
        $events[3]->datum = "8.11.2012";
        $events[3]->fbUrl = "https://www.facebook.com/myEvents.vse.cz";
        $events[3]->kapacita = 300;
        $events[3]->konec = "20:00";
        $events[3]->misto = "RB 101";
        $events[3]->nazev = "Setkání filatelistů 2";
        $events[3]->popis = "Setkání filatelistů v RB 101. Bude sranda.";
        $events[3]->sponzor = "";
        $events[3]->zacatek = "19:00";
        
        $events[4] = new Event();
        $events[4]->id = 4;
        $events[4]->datum = "12.11.2012";
        $events[4]->fbUrl = "https://www.facebook.com/myEvents.vse.cz";
        $events[4]->kapacita = 300;
        $events[4]->konec = "20:00";
        $events[4]->misto = "RB 101";
        $events[4]->nazev = "Po stopách modrého mauricia";
        $events[4]->popis = "Setkání filatelistů v RB 101. Bude sranda.";
        $events[4]->sponzor = "";
        $events[4]->zacatek = "19:00";
        
        $events[5] = new Event();
        $events[5]->id = 5;
        $events[5]->datum = "30.11.2012";
        $events[5]->fbUrl = "https://www.facebook.com/myEvents.vse.cz";
        $events[5]->kapacita = 300;
        $events[5]->konec = "20:00";
        $events[5]->misto = "RB 101";
        $events[5]->nazev = "Setkání filatelistů 3";
        $events[5]->popis = "Setkání filatelistů v RB 101. Bude sranda.";
        $events[5]->sponzor = "";
        $events[5]->zacatek = "19:00";
        
        return $events;
    }
}
