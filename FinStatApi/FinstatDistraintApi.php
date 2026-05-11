<?php

namespace FinStat\Api;

use DateTime;
use FinStat\Client\AbstractFinstatApi;
use FinStat\ViewModel\Distraint\Bailiff;
use FinStat\ViewModel\Distraint\Debtor;
use FinStat\ViewModel\Distraint\DistraintDetailResult;
use FinStat\ViewModel\Distraint\DistraintDetailResults;
use FinStat\ViewModel\Distraint\DistraintPreview;
use FinStat\ViewModel\Distraint\DistraintResult;

/**
 * Client for the Distraint (Centrálny register exekúcií) endpoints.
 *
 * Mirrors `FinstatApi.ApiDistraintClient` in the C# client. Five endpoints:
 *
 *   - distraintSearch          → DistraintResult  (pre-check, lighter quota)
 *   - distraintResults         → DistraintResult  (full results, charges)
 *   - distraintResultsByToken  → DistraintResult  (re-fetch by token)
 *   - distraintDetail          → DistraintDetailResults
 *   - distraintStoredDetail    → DistraintDetailResults
 *
 * All five share the SK API hash convention used by other endpoints:
 * `sha256("SomeSalt+{apiKey}+{privateKey}++{hashParameter}+ended")`.
 *
 * @package FinStat\Api
 */
class FinstatDistraintApi extends AbstractFinstatApi
{
    /**
     * Pre-check search — same parameter set as RequestDistraintResults but
     * billed under a lighter quota. Use to confirm hits before paying for
     * the full result.
     *
     * @param string|null $ico
     * @param string|null $surname
     * @param string|null $dateOfBirth Pass as `Y-m-d` (the server expects a string).
     * @param string|null $city
     * @param string|null $companyName
     * @param string|null $fileReference
     * @param bool $json
     * @return DistraintResult|object|null Typed result (XML) or stdClass (JSON).
     */
    public function RequestDistraintSearch(
        $ico = null,
        $surname = null,
        $dateOfBirth = null,
        $city = null,
        $companyName = null,
        $fileReference = null,
        $json = false
    ) {
        return $this->doSearch(
            'distraintSearch',
            $ico, $surname, $dateOfBirth, $city, $companyName, $fileReference,
            $json
        );
    }

    /**
     * Full distraint search.
     *
     * @param string|null $ico
     * @param string|null $surname
     * @param string|null $dateOfBirth
     * @param string|null $city
     * @param string|null $companyName
     * @param string|null $fileReference
     * @param bool $json
     * @return DistraintResult|object|null
     */
    public function RequestDistraintResults(
        $ico = null,
        $surname = null,
        $dateOfBirth = null,
        $city = null,
        $companyName = null,
        $fileReference = null,
        $json = false
    ) {
        return $this->doSearch(
            'distraintResults',
            $ico, $surname, $dateOfBirth, $city, $companyName, $fileReference,
            $json
        );
    }

    /**
     * Re-fetch a previous search by its token (idempotent — no extra credit).
     *
     * @param string $token Token returned in `DetailToken` on prior results.
     * @param bool $json
     * @return DistraintResult|object|null
     */
    public function RequestDistraintResultsByToken($token, $json = false)
    {
        $detail = $this->DoRequest('distraintResultsByToken', array(
            'token' => $token,
        ), $token, $json);

        if ($detail === false || $detail === null) {
            return null;
        }
        return $json ? $detail : $this->parseDistraintResult($detail);
    }

    /**
     * Fetch full distraint detail for one or more `DetailId`s under a token.
     *
     * @param string $token DetailToken from prior search.
     * @param int[] $ids List of DetailIds to expand.
     * @param bool $json
     * @return DistraintDetailResults|object|null
     */
    public function RequestDistraintDetail($token, array $ids, $json = false)
    {
        // Hash input must match C# behaviour: token + ids concatenated with no
        // separator (see ApiDistraintClient.RequestDistraintDetail).
        $idsConcat = '';
        $idsParam = '';
        foreach ($ids as $id) {
            $idsConcat .= $id;
            $idsParam .= ($idsParam !== '' ? ',' : '') . $id;
        }

        $detail = $this->DoRequest('distraintDetail', array(
            'token' => $token,
            'ids' => $idsParam,
        ), $token . $idsConcat, $json);

        if ($detail === false || $detail === null) {
            return null;
        }
        return $json ? $detail : $this->parseDistraintDetailResults($detail);
    }

    /**
     * Fetch a stored distraint detail by its persistent `StoredDetailId`
     * (server-side cache key — survives token expiry).
     *
     * @param string $id
     * @param bool $json
     * @return DistraintDetailResults|object|null
     */
    public function RequestDistraintStoredDetail($id, $json = false)
    {
        $detail = $this->DoRequest('distraintStoredDetail', array(
            'id' => $id,
        ), $id, $json);

        if ($detail === false || $detail === null) {
            return null;
        }
        return $json ? $detail : $this->parseDistraintDetailResults($detail);
    }

    /**
     * Pipe-separated `search` parameter as the C# client builds it:
     *   `{ico}|{surname}|{dateOfBirth}|{city}|{companyName}|{fileReference}`.
     * The hash is taken over this exact string.
     *
     * @return DistraintResult|object|null
     */
    private function doSearch(
        $endpoint, $ico, $surname, $dateOfBirth, $city, $companyName, $fileReference, $json
    ) {
        $search = sprintf('%s|%s|%s|%s|%s|%s',
            (string)$ico,
            (string)$surname,
            (string)$dateOfBirth,
            (string)$city,
            (string)$companyName,
            (string)$fileReference
        );

        $detail = $this->DoRequest($endpoint, array(
            'search' => $search,
        ), $search, $json);

        if ($detail === false || $detail === null) {
            return null;
        }
        return $json ? $detail : $this->parseDistraintResult($detail);
    }

    // ---------------------------------------------------------------------
    // XML parsers — map SimpleXMLElement → typed view model classes.
    // ---------------------------------------------------------------------

    private function parseDistraintResult($detail): DistraintResult
    {
        $result = new DistraintResult();
        $result->Count = isset($detail->Count) ? (int)$detail->Count : null;
        $result->Distraints = array();
        if (!empty($detail->Distraints) && isset($detail->Distraints->DistraintPreview)) {
            foreach ($detail->Distraints->DistraintPreview as $preview) {
                $result->Distraints[] = $this->parseDistraintPreview($preview);
            }
        }
        return $result;
    }

    private function parseDistraintDetailResults($detail): DistraintDetailResults
    {
        $result = new DistraintDetailResults();
        $result->DistraintDetails = array();
        if (!empty($detail->DistraintDetails) && isset($detail->DistraintDetails->DistraintDetailResult)) {
            foreach ($detail->DistraintDetails->DistraintDetailResult as $row) {
                $result->DistraintDetails[] = $this->parseDistraintDetailResult($row);
            }
        }
        return $result;
    }

    /**
     * Populate the preview shape — both `DistraintPreview` and `DistraintDetailResult`
     * inherit these fields (C# `DistraintDetailResult : DistraintPreview`).
     *
     * @param \SimpleXMLElement $element
     * @param DistraintPreview|null $target
     * @return DistraintPreview
     */
    private function parseDistraintPreview($element, $target = null): DistraintPreview
    {
        $o = $target ?? new DistraintPreview();
        $o->Code = (string)$element->Code;
        $o->TypeOfAuthorisation = isset($element->TypeOfAuthorisation) ? (int)$element->TypeOfAuthorisation : null;
        $o->Created = $this->parseDate($element->Created);
        $o->DetailId = isset($element->DetailId) ? (int)$element->DetailId : null;
        $o->DetailToken = (string)$element->DetailToken;
        $o->StoredDetailId = (string)$element->StoredDetailId;
        $o->Debtors = array();
        if (!empty($element->Debtors) && isset($element->Debtors->Debtor)) {
            foreach ($element->Debtors->Debtor as $d) {
                $o->Debtors[] = $this->parseDebtor($d);
            }
        }
        $o->Pledgers = array();
        if (!empty($element->Pledgers) && isset($element->Pledgers->Debtor)) {
            foreach ($element->Pledgers->Debtor as $p) {
                $o->Pledgers[] = $this->parseDebtor($p);
            }
        }
        return $o;
    }

    private function parseDistraintDetailResult($element): DistraintDetailResult
    {
        // Inherit preview fields, then add detail-only ones (mirrors C# inheritance).
        $o = new DistraintDetailResult();
        $this->parseDistraintPreview($element, $o);

        $o->Court = (string)$element->Court;
        $o->DateOfAuthorisation = $this->parseDate($element->DateOfAuthorisation);
        $o->CourtCode = (string)$element->CourtCode;
        $o->EnforcementDetails = (string)$element->EnforcementDetails;
        $o->SumOutstanding = isset($element->SumOutstanding) ? (float)$element->SumOutstanding : null;
        $o->Currency = (string)$element->Currency;
        if (!empty($element->Bailiff)) {
            $o->Bailiff = $this->parseBailiff($element->Bailiff);
        }
        return $o;
    }

    private function parseDebtor($element): Debtor
    {
        $o = new Debtor();
        $o->Type = (string)$element->Type;
        $o->Name = (string)$element->Name;
        $o->Surname = (string)$element->Surname;
        $o->DateOfBirth = $this->parseDate($element->DateOfBirth);
        $o->IdentificationNumber = (string)$element->IdentificationNumber;
        $o->ICO = (string)$element->ICO;
        $o->CompanyName = (string)$element->CompanyName;
        $o->AddressType = (string)$element->AddressType;
        $o->Street = (string)$element->Street;
        $o->City = (string)$element->City;
        $o->ZIP = (string)$element->ZIP;
        return $o;
    }

    private function parseBailiff($element): Bailiff
    {
        $o = new Bailiff();
        $o->Id = isset($element->Id) && (string)$element->Id !== '' ? (int)$element->Id : null;
        $o->Name = (string)$element->Name;
        $o->Street = (string)$element->Street;
        $o->ZIP = (string)$element->ZIP;
        $o->City = (string)$element->City;
        return $o;
    }
}
