<?php
require_once(__DIR__ . '/Requests.php');
require_once(__DIR__ . '/AbstractFinstatApi.php');
require_once(__DIR__ . '/ViewModel/AutoCompleteResult.php');

class BaseFinstatApi extends AbstractFinstatApi
{
    public function RequestAutoLogin($redirecturl, $email = null, $json = false)
    {
        $data = array('url' => $redirecturl);
        if (!empty($email)) {
            $data['email'] = $email;
        }

        $detail = $this->DoRequest("autologin", $data, "autologin", $json);

        return (string)$detail;
    }

    public function RequestAutoComplete($query, $json = false)
    {
        $detail = $this->DoRequest("autocomplete", array('query' => $query), $query, $json);

        if(!$json) {
            return $this->parseAutoComplete($detail);
        } else
        {
            return $detail;
        }
    }

    protected function parseAutoComplete($detail)
    {
        if  ($detail === FALSE) {
            return $detail;
        }

        $response = new AutoCompleteResult();
        $response->Results = array();
        if (!empty($detail->Results) && !empty($detail->Results->Company)) {
            foreach($detail->Results->Company as $company) {
                $oc = new CompanyResult();
                $oc->Ico  = (string) $company->Ico;
                $oc->Name  = (string) $company->Name;
                $oc->City  = (string) $company->City;
                $oc->Cancelled = (((string)$company->Cancelled) == "true");
                $response->Results[] = $oc;
            }
        }
        $response->Suggestions = array();
        if (!empty($detail->Suggestions) && !empty($detail->Suggestions->string)) {
            foreach($detail->Suggestions->string as $string) {
                $response->Suggestions[] = (string)$string;
            }
        }

        return $response;
    }

    protected function parseAbstractBase($detail , $response = null)
    {
        if  ($detail === FALSE) {
            return $detail;
        }
        $response = ($response == null)? new AbstractBaseResult() : $response;
        $response = $this->parseFullAddress($detail, $response);
        $response->Ico                  = (string)$detail->Ico;
        $response->IcDPH                = (string)$detail->IcDPH;
        $response->Activity             = (string)$detail->Activity;
        $response->Created              = $this->parseDate($detail->Created);
        $response->Cancelled            = $this->parseDate($detail->Cancelled);
        $response->Url                  = (string)$detail->Url;
        $response->Warning              = "{$detail->Warning}"  == 'true' ;
        $response->WarningUrl           = (string)$detail->WarningUrl;

        return $response;
    }
}
