<?php

namespace App\Services;

use App\Models\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AnafService
{
    /**
     * ANAF API endpoint for VAT verification
     */
    const ANAF_API_URL = 'https://webservicesp.anaf.ro/api/PlatitorTvaRest/v9/tva';

    /**
     * Maximum CUI per request (ANAF limitation)
     */
    const MAX_CUI_PER_REQUEST = 100;

    /**
     * Rate limit: 1 request per second
     */
    const RATE_LIMIT_SECONDS = 1;

    /**
     * Last request timestamp for rate limiting
     */
    private static $lastRequestTime = null;

    /**
     * Verify VAT status for one or multiple CUI numbers
     *
     * @param array|string $cui Single CUI or array of CUI numbers
     * @param string|null $date Date for verification (format: Y-m-d), defaults to today
     * @return array Response from ANAF API
     */
    public function verifyVatStatus($cui, $date = null)
    {
        // Convert single CUI to array
        if (!is_array($cui)) {
            $cui = [$cui];
        }

        // Validate CUI count
        if (count($cui) > self::MAX_CUI_PER_REQUEST) {
            throw new \InvalidArgumentException(
                'Maximum ' . self::MAX_CUI_PER_REQUEST . ' CUI numbers per request allowed'
            );
        }

        // Default date to today if not provided
        if (!$date) {
            $date = Carbon::now()->format('Y-m-d');
        }

        // Build request body
        $requestBody = [];
        foreach ($cui as $cuiNumber) {
            $requestBody[] = [
                'cui' => $this->sanitizeCui($cuiNumber),
                'data' => $date
            ];
        }

        // Apply rate limiting
        $this->applyRateLimit();

        try {
            // Make API request
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post(self::ANAF_API_URL, $requestBody);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('ANAF API Response', ['data' => $data]);
                return $data;
            } else {
                Log::error('ANAF API Error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                throw new \Exception('ANAF API request failed: ' . $response->status());
            }
        } catch (\Exception $e) {
            Log::error('ANAF API Exception', ['message' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Register or update client from ANAF data
     *
     * @param string $cui CUI number
     * @param string|null $date Date for verification
     * @return Client|null
     */
    public function registerClientFromAnaf($cui, $date = null)
    {
        $cui = $this->sanitizeCui($cui);
        
        // Get data from ANAF
        $anafData = $this->verifyVatStatus($cui, $date);

        if (!isset($anafData['found']) || empty($anafData['found'])) {
            Log::warning('Client not found in ANAF', ['cui' => $cui]);
            return null;
        }

        // Get first result (should be only one for single CUI)
        $clientData = $anafData['found'][0];

        return $this->saveClientFromAnafData($clientData);
    }

    /**
     * Save or update client in database from ANAF data
     *
     * @param array $anafData ANAF response data for a single client
     * @return Client
     */
    public function saveClientFromAnafData(array $anafData)
    {
        $dateGenerale = $anafData['date_generale'] ?? [];
        $inregistrareScpTva = $anafData['inregistrare_scop_Tva'] ?? [];
        $inregistrareRtvai = $anafData['inregistrare_RTVAI'] ?? [];
        $stareInactiv = $anafData['stare_inactiv'] ?? [];
        $inregistrareSplitTva = $anafData['inregistrare_SplitTVA'] ?? [];
        $adresaSediuSocial = $anafData['adresa_sediu_social'] ?? [];
        $adresaDomiciliuFiscal = $anafData['adresa_domiciliu_fiscal'] ?? [];

        // Find or create client by CUI
        $client = Client::where('cui', $dateGenerale['cui'] ?? null)->first();

        if (!$client) {
            $client = new Client();
        }

        // Update client data
        $client->cui = $dateGenerale['cui'] ?? null;
        $client->nume = $dateGenerale['denumire'] ?? null;
        $client->adresa = $dateGenerale['adresa'] ?? null;
        $client->nrRegCom = $dateGenerale['nrRegCom'] ?? null;
        $client->telefon = $dateGenerale['telefon'] ?? null;
        $client->fax = $dateGenerale['fax'] ?? null;
        $client->codPostal = $dateGenerale['codPostal'] ?? null;
        $client->act = $dateGenerale['act'] ?? null;
        $client->stare_inregistrare = $dateGenerale['stare_inregistrare'] ?? null;
        $client->data_inregistrare = $dateGenerale['data_inregistrare'] ?? null;
        $client->cod_CAEN = $dateGenerale['cod_CAEN'] ?? null;
        $client->iban = $dateGenerale['iban'] ?? null;
        $client->statusRO_e_Factura = ($dateGenerale['statusRO_e_Factura'] ?? false) ? 1 : 0;
        $client->organFiscalCompetent = $dateGenerale['organFiscalCompetent'] ?? null;
        $client->forma_de_proprietate = $dateGenerale['forma_de_proprietate'] ?? null;
        $client->forma_organizare = $dateGenerale['forma_organizare'] ?? null;
        $client->forma_juridica = $dateGenerale['forma_juridica'] ?? null;

        // VAT registration fields
        $client->scpTVA = ($inregistrareScpTva['scpTVA'] ?? false) ? 1 : 0;
        $perioade = $inregistrareScpTva['perioade_TVA'] ?? [];
        $client->data_inceput_ScpTVA = $perioade['data_inceput_ScpTVA'] ?? null;
        $client->data_sfarsit_ScpTVA = $perioade['data_sfarsit_ScpTVA'] ?? null;
        $client->data_anul_imp_ScpTVA = $perioade['data_anul_imp_ScpTVA'] ?? null;
        $client->mesaj_ScpTVA = $perioade['mesaj_ScpTVA'] ?? null;

        // TVA la incasare (cash accounting VAT)
        $client->dataInceputTvaInc = $inregistrareRtvai['dataInceputTvaInc'] ?? null;
        $client->dataSfarsitTvaInc = $inregistrareRtvai['dataSfarsitTvaInc'] ?? null;
        $client->dataActualizareTvaInc = $inregistrareRtvai['dataActualizareTvaInc'] ?? null;
        $client->dataPublicareTvaInc = $inregistrareRtvai['dataPublicareTvaInc'] ?? null;
        $client->tipActTvaInc = $inregistrareRtvai['tipActTvaInc'] ?? null;
        $client->statusTvaIncasare = ($inregistrareRtvai['statusTvaIncasare'] ?? false) ? 1 : 0;

        // Inactive/Reactive status
        $client->dataInactivare = $stareInactiv['dataInactivare'] ?? null;
        $client->dataReactivare = $stareInactiv['dataReactivare'] ?? null;
        $client->dataPublicare = $stareInactiv['dataPublicare'] ?? null;
        $client->dataRadiere = $stareInactiv['dataRadiere'] ?? null;
        $client->statusInactivi = ($stareInactiv['statusInactivi'] ?? false) ? 1 : 0;

        // Split TVA (deferred VAT payment)
        $client->dataInceputSplitTVA = $inregistrareSplitTva['dataInceputSplitTVA'] ?? null;
        $client->dataAnulareSplitTVA = $inregistrareSplitTva['dataAnulareSplitTVA'] ?? null;
        $client->statusSplitTVA = ($inregistrareSplitTva['statusSplitTVA'] ?? false) ? 1 : 0;

        // Social headquarters address
        $client->sdenumire_Strada = $adresaSediuSocial['sdenumire_Strada'] ?? null;
        $client->snumar_Strada = $adresaSediuSocial['snumar_Strada'] ?? null;
        $client->sdenumire_Localitate = $adresaSediuSocial['sdenumire_Localitate'] ?? null;
        $client->scod_Localitate = $adresaSediuSocial['scod_Localitate'] ?? null;
        $client->sdenumire_Judet = $adresaSediuSocial['sdenumire_Judet'] ?? null;
        $client->scod_Judet = $adresaSediuSocial['scod_Judet'] ?? null;
        $client->scod_JudetAuto = $adresaSediuSocial['scod_JudetAuto'] ?? null;
        $client->stara = $adresaSediuSocial['stara'] ?? null;
        $client->sdetalii_Adresa = $adresaSediuSocial['sdetalii_Adresa'] ?? null;
        $client->scod_Postal = $adresaSediuSocial['scod_Postal'] ?? null;

        // Fiscal domicile address
        $client->ddenumire_Strada = $adresaDomiciliuFiscal['ddenumire_Strada'] ?? null;
        $client->dnumar_Strada = $adresaDomiciliuFiscal['dnumar_Strada'] ?? null;
        $client->ddenumire_Localitate = $adresaDomiciliuFiscal['ddenumire_Localitate'] ?? null;
        $client->dcod_Localitate = $adresaDomiciliuFiscal['dcod_Localitate'] ?? null;
        $client->ddenumire_Judet = $adresaDomiciliuFiscal['ddenumire_Judet'] ?? null;
        $client->dcod_Judet = $adresaDomiciliuFiscal['dcod_Judet'] ?? null;
        $client->dcod_JudetAuto = $adresaDomiciliuFiscal['dcod_JudetAuto'] ?? null;
        $client->dtara = $adresaDomiciliuFiscal['dtara'] ?? null;
        $client->ddetalii_Adresa = $adresaDomiciliuFiscal['ddetalii_Adresa'] ?? null;
        $client->dcod_Postal = $adresaDomiciliuFiscal['dcod_Postal'] ?? null;

        // Save last ANAF check date
        $client->last_anaf_check = now();

        $client->save();

        Log::info('Client saved/updated from ANAF', ['cui' => $client->cui, 'id' => $client->idcl]);

        return $client;
    }

    /**
     * Apply rate limiting (1 request per second)
     */
    private function applyRateLimit()
    {
        if (self::$lastRequestTime !== null) {
            $timeSinceLastRequest = microtime(true) - self::$lastRequestTime;
            $waitTime = self::RATE_LIMIT_SECONDS - $timeSinceLastRequest;

            if ($waitTime > 0) {
                usleep($waitTime * 1000000); // Convert to microseconds
            }
        }

        self::$lastRequestTime = microtime(true);
    }

    /**
     * Sanitize CUI number (remove RO prefix, spaces, etc.)
     *
     * @param string $cui
     * @return string
     */
    private function sanitizeCui($cui)
    {
        // Remove RO prefix if present
        $cui = preg_replace('/^RO/i', '', $cui);
        
        // Remove spaces and special characters
        $cui = preg_replace('/[^0-9]/', '', $cui);
        
        return $cui;
    }

    /**
     * Batch register multiple clients from ANAF
     * Respects the 100 CUI per request limit
     *
     * @param array $cuiList Array of CUI numbers
     * @param string|null $date Date for verification
     * @return array Array of registered clients
     */
    public function batchRegisterClients(array $cuiList, $date = null)
    {
        $registeredClients = [];
        $chunks = array_chunk($cuiList, self::MAX_CUI_PER_REQUEST);

        foreach ($chunks as $chunk) {
            try {
                $anafData = $this->verifyVatStatus($chunk, $date);

                if (isset($anafData['found']) && !empty($anafData['found'])) {
                    foreach ($anafData['found'] as $clientData) {
                        $client = $this->saveClientFromAnafData($clientData);
                        $registeredClients[] = $client;
                    }
                }

                if (isset($anafData['notFound']) && !empty($anafData['notFound'])) {
                    Log::warning('CUI numbers not found in ANAF', ['notFound' => $anafData['notFound']]);
                }
            } catch (\Exception $e) {
                Log::error('Error in batch registration', [
                    'chunk' => $chunk,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $registeredClients;
    }

    /**
     * Check if client data needs refresh (older than specified days)
     *
     * @param Client $client
     * @param int $days Number of days before refresh is needed
     * @return bool
     */
    public function needsRefresh(Client $client, $days = 30)
    {
        if (!$client->last_anaf_check) {
            return true;
        }

        $lastCheck = Carbon::parse($client->last_anaf_check);
        return $lastCheck->diffInDays(now()) >= $days;
    }

    /**
     * Refresh client data from ANAF if needed
     *
     * @param Client $client
     * @param int $days Days before refresh
     * @param string|null $date Date for verification
     * @return Client
     */
    public function refreshIfNeeded(Client $client, $days = 30, $date = null)
    {
        if ($this->needsRefresh($client, $days)) {
            Log::info('Refreshing client data from ANAF', ['cui' => $client->cui]);
            return $this->registerClientFromAnaf($client->cui, $date);
        }

        return $client;
    }
}
