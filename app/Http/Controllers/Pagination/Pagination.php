<?php

namespace App\Http\Controllers\Pagination;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Curl\Fetch;
use App\Http\Controllers\DOM\DOM;
use App\Http\Controllers\ProcessControll;
use Illuminate\Http\Request;

class Pagination extends Controller
{


    public $links_to_crawl = array();
    public $subpage;
    public $pagination_data = '';
    public $paginations_request = '';
    public $single_links_request = '';
    public $request;
    public $raw_request;
    public $dom_selectors;
    public $pagers_links;

    public function __construct(Request $request) {

        $this->raw_request = $request;
        $this->request = $request->toArray();
        foreach ($this->request as $key => $value) {
            $this->request[$key] = json_decode($value);
        }

        $this->subpage = new Subpages();
    }

    public function startGrabbingPagination() {

        $this->rebuildPaginationRequest();
        $this->buildDomSelectors();
        $this->pagers_links = $this->buildPagersLinks();

        $curl_fetch = new Fetch($this->pagers_links);
        $dom = new DOM($this->dom_selectors);
        $extracted_pagination_content = $dom->initializeDOM($curl_fetch->getPageData());


        $this->getAllFoundSubpagesLinks($extracted_pagination_content);
        $this->rebuildSingleLinksRequest();

        $this->subpage->scrapSingleLinks($this->single_links_request);
    }

    protected function rebuildSingleLinksRequest() {
        $this->single_links_request = array(
            'acceptKeywordsBody' => $this->request['acceptKeywordsBody'],
            'acceptKeywordsOther' => $this->request['acceptKeywordsOther'],
            'rejectKeywordsBody' => $this->request['rejectKeywordsBody'],
            'rejectKeywordsOther' => $this->request['rejectKeywordsOther'],
            'acceptKeywordsBody' => $this->request['acceptKeywordsBody'],
            'querySelectorMain' => $this->request['querySelectorMain'],
            'querySelectorOther' => $this->request['querySelectorOther']
        );
        $this->single_links_request['links'] = $this->links_to_crawl;

    }

    protected function rebuildPaginationRequest() {

        $this->paginations_request = array(
            'startPage' => reset($this->request['startPage']),
            'endPage' => reset($this->request['endPage']),
            'pageIterator' => reset($this->request['pageIterator']),
            'pagesPattern' => reset($this->request['pagesPattern']),
            'pagination_links' => $this->request['pagination_links'],
            'querySelectorMain' => $this->request['querySelectorMain'],
            'querySelectorOther' => $this->request['querySelectorOther']
        );


    }

    protected function getAllFoundSubpagesLinks($extracted_data) {

        for ($x = 0; $x <= count($extracted_data['content']) - 1; $x++) {
            $extracted_data['content'][$x]['dom_content']['main']->each(
                function ($element, $num) {
                    global $global_domain_name;
                    $subpage_link = $this->subpage->extractSubpageLinkFromEachMatch($element);
                    array_push($this->links_to_crawl, $global_domain_name . $subpage_link);
                });
        }
    }

    protected function buildDomSelectors() {
        $this->dom_selectors = array(
            'querySelectorMain' => $this->request['subpagesLinkExtractionPattern']
        );
    }

    private function buildPagersLinks() {
        $rebuilded_links = array();

        foreach ($this->request['pagination_links'] as $one_pagination_link) {
            $multiply = 1;

            for ($x = $this->paginations_request['startPage']; $x <= $this->paginations_request['endPage']; $x++) {
                array_push($rebuilded_links, $this->buildOnePagerLink($multiply, $one_pagination_link));
                $multiply++;
            }

        }

        return $rebuilded_links;
    }

    private function buildOnePagerLink($multiply = false, $one_pagination_link = false) {

        if (!(bool)$multiply) {
            $page_num = $this->paginations_request['startPage'];
        } else {
            $page_num = (int)$this->paginations_request['startPage'] + (int)$this->paginations_request['pageIterator'] * $multiply;
        }

        return trim(str_replace($this->paginations_request['pagesPattern'], $page_num, $one_pagination_link));
    }


}
