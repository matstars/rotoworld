<?php
/*
This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

The information obtained with this package are the intellectual 
property of Rotoworld.com and NBCSports. This is to be used for 
educational purposes and you should consult with Rotoworld.com or 
NBC Sports if you are going to use this or information obtained 
with this anyhow and anywhere.

*/

namespace Rotoworld;
/**
 * Class Rotoworld News
 */

class Rotoworld {
    
    protected $url;
    protected $itemIdentifier;
    protected $newsObject;
    protected $containerId;

     /**
      * Constructor allows defining of rotoworld URL and itemIdentifier right in the constructor, mutator methods also exist.
      * @param string $url rotoworld url to scrape
      * @param string $itemIdentifier jQuery style identifier
      * @return void
      */

    public function __construct($url = 'http://www.rotoworld.com/sports/mlb/baseball', $itemIdentifier = '.pb'){
        $this->setUrl($url);
        $this->setItemIdentifier($itemIdentifier);
    }

     /**
      * Set the Rotoworld URL to scrape
      * @param string $url rotoworld url to scrape
      * @return void
      */

    public function setUrl($url){
        $this->url = $url;
    }

    /**
      * Set the Rotoworld wrapping identifier (in jQuery format e.g. class of pb should be '.pb')
      * @param string $itemIdentifier jQuery style identifier
      * @return void
      */

    public function setItemIdentifier($itemIdentifier){
        $this->itemIdentifier = $itemIdentifier;
    }

    /**
      * Get the data
      * @return array of data objects
      */

    public function get(){
        $dataArray = array();
        $html = \SimpleHtmlDom\file_get_html($this->url);
        foreach($html->find($this->itemIdentifier) as $element) {
            $data = $this->parseData($element);
            $dataArray[] = $data;
        }
        return $dataArray;
    }

    /**
      * Convert raw DOM data to a well defined object containing player news and meta information
      * @param object DOM object containing raw player news and meta data
      * @return object well defined object containing player news and meta information
      */

    private function parseData($element){
        foreach($element->find('.headline, .report, .impact, .info') as $elementContent){
             $data = new \stdClass;
             $class = $elementContent->attr['class'];
                switch($class){
                    case 'headline':
                        $playerInfo = explode(' - ', $elementContent->plaintext);
                        $linkInfo = explode('/', $elementContent->find('a')[0]->attr['href']);
                        $data->name = trim($playerInfo[0]);
                        $data->position = trim($playerInfo[1]);
                        $data->team = trim($playerInfo[2]);
                        $data->id = trim($linkInfo[3]);
                        $data->nameDashDelimited = trim($linkInfo[4]);
                    break;
                    case 'report':
                        $data->report = trim($elementContent->plaintext);
                    break;

                    case 'impact':
                        $data->impact = trim($elementContent->plaintext);
                    break;

                    case 'info':
                        $data->related = null;
                        $relatedRaw = $elementContent->find('.related')[0]->find('a');
                        foreach($relatedRaw as $relatedItem){
                            if ($data->related) $data->related .= ',';
                            $data->related .= trim($relatedItem->plaintext);
                        }
                        if (isset($elementContent->find('.source a')[0]->attr['href'])){
                            $data->sourceURL = trim($elementContent->find('.source a')[0]->attr['href']);
                            $data->sourceName = trim($elementContent->find('.source a')[0]->plaintext);
                        }
                        $date = $elementContent->find('.date')[0]->plaintext;
                        $data->date = strtotime(str_replace(' - ', ',', $date)); //convert the date by making it strtotime readable
                    break;
                }
           }
           return $data;
    }
}