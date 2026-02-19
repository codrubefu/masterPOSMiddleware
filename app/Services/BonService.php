<?php

namespace App\Services;

use App\Models\Company;

class BonService
{
    protected $casaFiles;
    protected $templatePath;

    public function __construct()
    {
        $this->casaFiles = config('casa.file');
        $this->templatePath = __DIR__ . '/BonExample/';
    }

    /**
     * Get the next bon number and increment the stored value.
     *
     * @return int
     * @throws \Exception
     */
    public function getNextBonNumber(): int
    {
        $bonFilePath = base_path('bon');

        if (!file_exists($bonFilePath)) {
            throw new \Exception('Bon file does not exist.');
        }

        $bonNo = file_get_contents($bonFilePath);
        if ($bonNo === false) {
            throw new \Exception('Failed to read bon file.');
        }

        $currentBon = intval($bonNo);
        $nextBon = $currentBon + 1;

        if (file_put_contents($bonFilePath, $nextBon) === false) {
            throw new \Exception('Failed to update bon file.');
        }

        return $nextBon;
    }

    /**
     * Load template content from file
     *
     * @param string $templateFileName
     * @return string
     * @throws \Exception
     */
    private function loadTemplate(string $templateFileName): string
    {
        $templateFile = $this->templatePath . $templateFileName;
        
        if (!file_exists($templateFile)) {
            throw new \Exception("Template file not found: {$templateFileName}");
        }
        
        $content = file_get_contents($templateFile);
        
        if ($content === false) {
            throw new \Exception("Failed to read template file: {$templateFileName}");
        }
        
        // Convert escape sequences like \t to actual characters
        return stripcslashes($content);
    }

    /**
     * Write formatted content to bon file
     *
     * @param int $casa
     * @param string $content
     * @return bool
     * @throws \Exception
     */
    private function writeToBonFile(int $casa, string $content): bool
    {
        // Check if casa path exists in config and the folder exists on filesystem
        if (isset($this->casaFiles[$casa]['path']) && file_exists($this->casaFiles[$casa]['path'])) {
            $casaPath = $this->casaFiles[$casa]['path'];
        } else {
            $casaPath = storage_path('bon');
            // Create directory if it doesn't exist
            if (!file_exists($casaPath)) {
                mkdir($casaPath, 0755, true);
            }
        }
        
        $entryFilePath = $casaPath . '/bon.txt';
        if (file_put_contents($entryFilePath, $content) === false) {
            throw new \Exception('Failed to write to bon file.');
        }

        return true;
    }

    /**
     * Write new entry line (product with name, quantity, price)
     *
     * @param int $casa
     * @param array $data
     * @return void
     * @throws \Exception
     */
    public function writeNewEntry($casa, $data, $isSgr): void
    {
        $template = $this->loadTemplate('bonline.txt');
        $content = sprintf($template, $data['name'], $data['departament'], $data['price']);
        if( !$isSgr ){
          $this->writeToBonFile($casa, $content);
        }
    }

    /**
     * Write subtotal line
     *
     * @param int $casa
     * @param array $data
     * @return void
     * @throws \Exception
     */
    public function writeSubtotal( $data): void
    {
        $template = $this->loadTemplate('subtotal.txt');
        $content = sprintf($template, $data['subtotal']);
        $this->writeToBonFile($data['casa'], $content);
    }

    public function writeBonFinal($data): int
    {
       
        $lines = [];
        if($data['type']=='cash'){
            $code = '53,0';
        }
        elseif($data['type']=='card'){
            $code = '53,1';
        }
        else{
            $cardAmount = $data['cardAmount'] ?? 0;
            $cashAmount = $data['numerarAmount'] ?? 0;
            $codes[] = sprintf("53,%s	%s", 0, $cashAmount);
            $codes[] = sprintf("53,%s	%s", 1, $cardAmount);
            $code = implode("\n", $codes);
        }
        
        foreach ($data['items'] as $item) {
            $lines[] = sprintf(
                "49,%s	%s	%s	%s			%s	buc	",
                $item['product']['name'],
                $item['product']['departament'],
                $item['unitPrice'],
                $item['qty'].'.000',
                $item['product']['departament']
            );
        }
        $itemsContent = implode("\n", $lines);
        if(isset($data['customer']['type']) && $data['customer']['type']=='pj'){
            $template = $this->loadTemplate('bonfinalpj.txt');
            $finalContent = sprintf($template, $itemsContent,$code,$data['customer']['cnpcui'] ?? '', $data['customer']['lastName'] ?? '');
            
        }
        else{
            $template = $this->loadTemplate('bonfinal.txt');
            $finalContent = sprintf($template, $itemsContent,$code);

        }
        return $this->writeToBonFile($data['casa'], $finalContent);
         
    }

    public function writeRaportZ($data): int
    {
        $template = $this->loadTemplate('raportz.txt');
        $content = sprintf($template, $data['casa']);
        return $this->writeToBonFile($data['casa'], $content);
    }

    public function writeRaportX($data): bool
    {
        $template = $this->loadTemplate('raportx.txt');
        $content = sprintf($template, $data['casa']);
        $isOk = $this->writeToBonFile($data['casa'], $content);
        if ($isOk) {
            $this->archiveDay($data['casa']);
            return true;
        }

        return false;
    }

    protected function archiveDay($casa){
        $okFolder = $this->casaFiles[$casa]['path'].'\BONOK';
        $archiveFolder = $this->casaFiles[$casa]['path'].'\BONOK'.date('Ymd');
        
        // If archive folder exists, add hour and minutes
        if (file_exists($archiveFolder)) {
            $archiveFolder = $this->casaFiles[$casa]['path'].'\BONOK'.date('Ymd_His');
        }
        
        if (file_exists($okFolder)) {
            rename($okFolder, $archiveFolder);
        }
        
        // Create new BONOK folder
        if (!file_exists($okFolder)) {
            mkdir($okFolder, 0755, true);
        }
    }

    public function isPaymentDone($casa): bool
    {
        $company = Company::first();
        $bon = $this->casaFiles[$casa]['path'].'\BONERR\bonerr'.$company->nrbfdude .'.txt';
        if( !file_exists($bon) ){
            return false;
        }

        $bonContent = file_get_contents($bon);
        // Check if bonContent contains both Command = 56 and Command = 53
        $hasCommand56 = strpos($bonContent, 'Command = 56') !== false;
        $hasCommand53 = strpos($bonContent, 'Command = 53') !== false;
        
        return $hasCommand56 && $hasCommand53;
    }

}
